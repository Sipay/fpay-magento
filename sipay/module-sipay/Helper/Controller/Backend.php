<?php

namespace Sipay\Sipay\Helper\Controller;

class Backend
{

  protected $helper;
  protected $checkout;
  protected $result_factory;
  protected $transaction_helper;
  protected $customer_session;
  protected $checkout_session;

  public function __construct(
    \Sipay\Sipay\Helper\Data                               $helper,
    \Magento\Checkout\Helper\Data                           $checkoutHelper,
    \Magento\Framework\Controller\Result\JsonFactory        $resultJsonFactory,
    \Sipay\Sipay\Helper\Transaction\Data                   $transactionHelper,
    \Magento\Framework\Message\ManagerInterface             $messageManager,
    \Magento\Customer\Model\Session                         $customerSession,
    \Magento\Checkout\Model\Session                         $checkoutSession,
    \Magento\Framework\App\State                            $state,
    \Magento\Framework\Filesystem\DirectoryList             $dir,
    \Magento\Sales\Model\OrderRepository                    $orderRepository,
    \Magento\Store\Model\StoreManagerInterface              $storeManager,
    \Magento\Framework\Registry                             $magentoRegistry,
    \Sipay\Sipay\Helper\Constants                          $sipayConstants,
    \Magento\Quote\Api\CartRepositoryInterface              $quoteRepository,
    \Magento\Sales\Model\Order\Email\Sender\OrderSender     $emailOrderSender
  ){
    $this->helper               = $helper;
    $this->checkout             = $checkoutHelper;
    $this->result_factory       = $resultJsonFactory;
    $this->transaction_helper   = $transactionHelper;
    $this->_messageManager      = $messageManager;
    $this->customer_session     = $customerSession;
    $this->checkout_session     = $checkoutSession;
    $this->state                = $state;
    $this->dir                  = $dir;
    $this->order_repository     = $orderRepository;
    $this->store_manager        = $storeManager;
    $this->registry             = $magentoRegistry;
    $this->sipay_constants      = $sipayConstants;
    $this->quote_repository     = $quoteRepository;
    $this->email_order_sender   = $emailOrderSender;

  }

  public function addOrderInfo(&$pwall_request, $quote){
    $pwall_request->setOrderId(strval($quote->getId()));
    $pwall_request->setAmount($quote->getGrandTotal());
    $pwall_request->setCurrency(($quote->getQuoteCurrencyCode() == "") || ($quote->getQuoteCurrencyCode()) ? $this->store_manager->getStore()->getCurrentCurrency()->getCode() : $quote->getQuoteCurrencyCode());
    $pwall_request->setGroupId(strval($quote->getCustomerId() ? $quote->getCustomerId() : 0));
    $pwall_request->setOriginalUrl($this->helper->getUrl("/"));
  }

  public function executeByMethod($jsonRequest, $referrer_url = 'checkout/cart'){
    $result       = $this->result_factory->create();
    $this->helper->debug("ON BACKEND EXECUTE: " . json_encode($jsonRequest));

    $this->customer_session->setSipayErrorRedirectUrl($referrer_url);

    $client = new \PWall\Client();
    $client->setEnvironment($this->helper->getConfig('payment/sipay_sipay/environment'));
    $client->setKey($this->helper->getConfig('payment/sipay_sipay/key'));
    $client->setResource($this->helper->getConfig('payment/sipay_sipay/resource'));
    $client->setSecret($this->helper->getConfig('payment/sipay_sipay/secret'));
    $client->setBackendUrl($this->helper->getUrl('sipay/payment'));
    $debug_enable = $this->helper->getConfig('payment/sipay_sipay/debug');
    $debug_path = $this->helper->getConfig('payment/sipay_sipay/debug_path');
    if($debug_enable == true){
      if($debug_path && $debug_path != ''){
        $client->setDebugFile($debug_path);
      }
    }

    $quote    = $this->getQuoteFromCheckoutorSession();
    $customer = $this->customer_session->getCustomer();
    if($quote->getId()){
      $request = new \PWall\Request(json_encode($jsonRequest), false);
    }else{
      $request = new \PWall\Request(json_encode($jsonRequest), true);
    }
    $this->addOrderInfo($request, $quote);

    if($request->isEcCreateOrder()||$request->hasUpdateAmount()){
      //add product info
      //       is_digital: true <- only if all element are digital
      $cart_info = $this->helper->getPaypalItemsInfo($quote);
      $request->setEcCartInfo($cart_info["items"], $cart_info["is_digital"], $cart_info["breakdown"]);
      $request->setAmount($cart_info["total"]);
      if($quote->getShippingAddress()){
        $request->setAmount($cart_info["total"]);
        // $request->setAmount($quote->getShippingAddress()->getSubtotal());
      }
    }

    //PSD2
    $this->helper->setPDS2Params($request, $quote, $customer);

    // $request->setPSD2Info($pds2_info["tra_enabled"], $pds2_info["tra_value"], $pds2_info["lwv_enabled"], $pds2_info["lwv_value"], $)

    $response = $client->proxy($request);


    if ($response->hasAddress() && !$response->hasUpdateAmount()) {
      //Set address to quote, set shipping method, collect rates
      try{
        $this->registry->register($this->sipay_constants::SIPAY_EC_NOREGION_ID, 1);
        $quote = $this->checkout->getQuote();
        $error = $this->helper->setAddressAndCollectRates($response, $quote);
        if($error){
          //$response->setUpdatedAmount(0);
          $response->setError($error);
          $result->setJsonData($response->toJSON());
          return $result;
        }else{
          //$cart_info = $this->helper->getPaypalItemsInfo($quote);
          $newquote = $this->checkout->getQuote();
          $response->setUpdatedAmount(floatval($newquote->getGrandTotal()));
        }
        
      }catch(\Exception $e){
        $response->setError($e->getMessage());
      }
    }

    $result->setJsonData($response->toJSON());

    if($response->isCreatePendingOrder() && !$this->customer_session->getSipayPendingPayment()){
      $orderId = $this->helper->placeOrderFromResponse($response, $quote);
      $this->customer_session->setSipayQuoteId($quote->getId());
      $this->customer_session->setSipayOrderId($orderId);
      $this->customer_session->setSipayPendingPayment(true);

      $this->checkout_session->setLastSuccessQuoteId($quote->getId());
      $this->checkout_session->setLastQuoteId($quote->getId());
      $this->checkout_session->setLastOrderId($orderId);
    }else{
      if ($response->canPlaceOrder()) {
        if ($this->customer_session->getSipayPendingPayment()) {
          $orderId = $this->customer_session->getSipayOrderId();
          $quoteId = $this->customer_session->getSipayQuoteId();
          $order = $this->order_repository->get($orderId);
          $this->helper->generateInvoice($order);
          $sipay_payment_info = $order->getPayment()->getAdditionalInformation();
          $sipay_payment_info["response"][0] = json_encode($response->getPaymentInfo());
          $order->getPayment()->setAdditionalInformation($sipay_payment_info);
          $isSuspectedFraud = $this->helper->isSuspectedFraud($order, $response);
          if (!$isSuspectedFraud) {
            $this->helper->setOrderStatus(
              $order,
              \Magento\Sales\Model\Order::STATE_PROCESSING,
              \Magento\Sales\Model\Order::STATE_PROCESSING,
              "Sipay payment processed successfully",
              true
            );
            //Send email
            $this->email_order_sender->send($order, true);
          }
          $this->customer_session->unsSipayQuoteId();
          $this->customer_session->unsSipayOrderId();
          $this->customer_session->unsSipayPendingPayment();

          $this->checkout_session->setLastSuccessQuoteId($quoteId);
          $this->checkout_session->setLastQuoteId($quoteId);
          $this->checkout_session->setLastOrderId($orderId);
        } else {
          $quote = $this->checkout->getQuote();
          $orderId = $this->helper->placeOrderFromResponse($response, $quote);
          $this->checkout_session->setLastSuccessQuoteId($quote->getId());
          $this->checkout_session->setLastQuoteId($quote->getId());
          $this->checkout_session->setLastOrderId($orderId);
        }
      }
    }
    
    return $result;
  }

  private function cleanUrlParams($url){
    //Clean url of query params    
    $parsed_url = parse_url($url);
    if (array_key_exists('query', $parsed_url)) {
      return preg_replace('/\?' . $parsed_url['query'] . '/', '', $url);
    } else {
      return $url;
    }
  }

  private function getQuoteFromCheckoutorSession(){
    if($this->checkout->getQuote()->getId()){
      $this->customer_session->unsSipayQuoteId();
      $this->customer_session->unsSipayOrderId();
      $this->customer_session->unsSipayPendingPayment();
      return $this->checkout->getQuote();
    }
    if ($this->customer_session->getSipayPendingPayment() && $this->customer_session->getSipayQuoteId()) {
      return $this->quote_repository->get($this->customer_session->getSipayQuoteId());
    }
    return $this->checkout->getQuote(); 
  }

}
