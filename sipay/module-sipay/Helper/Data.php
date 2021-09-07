<?php

namespace Sipay\Sipay\Helper;

class Data
{
  
  protected $config;
  protected $url_builder;
  protected $log;
  protected $request;
  protected $guest_payment;
  protected $customer_payment;
  protected $payment_method;
  protected $quote_repository;
  protected $quote_management;
  protected $quote_mask_factory;
  protected $order_repository;
  protected $cookie_manager;
  protected $cookie_metadata_factory;
  protected $shipping_config;

  public function __construct(
    \Magento\Framework\App\Helper\Context                             $context,
    \Magento\Quote\Api\CartRepositoryInterface                        $quoteRepository,
    \Magento\Checkout\Api\GuestPaymentInformationManagementInterface  $guestPaymentManagement,
    \Magento\Checkout\Api\PaymentInformationManagementInterface       $customerPaymentManagement,
    \Magento\Quote\Model\QuoteIdMaskFactory                           $quoteIdMaskFactory,
    \Magento\Quote\Model\QuoteManagement                              $quoteManagement,
    \Magento\Quote\Api\Data\PaymentInterface                          $paymentMethod,
    \Magento\Sales\Model\OrderRepository                              $orderRepository,
    \Magento\Framework\Stdlib\CookieManagerInterface                  $cookieManager,
    \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory            $cookieMetadataFactory,
    \Magento\CheckoutAgreements\Model\AgreementsProvider              $agreementsProvider,
    \Magento\Quote\Api\Data\PaymentExtensionFactory                   $paymentExtension,
    \Magento\Quote\Model\Quote\Address\Rate                           $shippingRate,
    \Magento\Checkout\Api\ShippingInformationManagementInterface      $shippingInformationManagement,
    \Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory    $shippingInformationFactory,
    \Magento\Quote\Model\QuoteValidator                               $quoteValidator,
    \Magento\Quote\Model\ShippingMethodManagement                     $shippingMethodManagement,
    \Magento\Sales\Model\Service\InvoiceService                       $invoiceService,
    \Magento\Framework\DB\Transaction                                 $transaction,
    \Magento\Directory\Model\PriceCurrency                            $priceFormat,
    \Magento\Sales\Model\Order\Email\Sender\OrderSender               $emailOrderSender,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory        $orderCollection
  ){
    $this->config                             = $context->getScopeConfig();
    $this->url_builder                        = $context->getUrlBuilder();
    $this->logger                             = $context->getLogger();
    $this->request                            = $context->getRequest();
    $this->quote_repository                   = $quoteRepository;
    $this->quote_management                   = $quoteManagement;
    $this->customer_payment                   = $customerPaymentManagement;
    $this->guest_payment                      = $guestPaymentManagement;
    $this->payment_method                     = $paymentMethod;
    $this->quote_mask_factory                 = $quoteIdMaskFactory;
    $this->order_repository                   = $orderRepository;
    $this->cookie_manager                     = $cookieManager;
    $this->cookie_metadata_factory            = $cookieMetadataFactory;
    $this->agreements_provider                = $agreementsProvider;
    $this->payment_extension                  = $paymentExtension;
    $this->shipping_rate                      = $shippingRate;
    $this->shipping_information_management    = $shippingInformationManagement;
    $this->shipping_information_factory       = $shippingInformationFactory;
    $this->quote_validator                    = $quoteValidator;    
    $this->shipping_method_management         = $shippingMethodManagement;
    $this->invoice_service                    = $invoiceService;
    $this->_transaction                       = $transaction;
    $this->price_format                       = $priceFormat;
    $this->email_order_sender                 = $emailOrderSender;
    $this->order_collection                   = $orderCollection;
  }

  public function getRequest(){
    return $this->request;
  }

  public function parseJSONRequest($content){
    return json_decode($content,true);
  }

  public function placeOrderFromResponse($response, $quote){
    // add payment method and convert quote to order
    $orderId = $this->convertQuoteToOrder($quote, $response);
    $this->invalidateCartCookie();
    return $orderId;
  }

  public function convertQuoteToOrder($quote, $response){
    $this->debug("CONVERT TO QUOTE ORDER: ".$quote->getId());
    // prepare payment method
    $this->payment_method->setMethod(\Sipay\Sipay\Helper\Constants::PAYMENT_METHOD_CODE);
    $this->payment_method->setAdditionalData(json_encode($response->getPaymentInfo()));
    if ($response->isCreatePendingOrder()) {
      $this->payment_method->setIsTransactionPending(true);
    }
    // set agreements ids
    $extension = $this->payment_extension->create();
    $extension->setAgreementIds($this->agreements_provider->getRequiredAgreementIds());
    $this->payment_method->setExtensionAttributes($extension);

    $idMask  = $this->quote_mask_factory->create();
    $cartId  = $idMask->load($quote->getId(), 'quote_id')->getMaskedId();
    $orderId = null;

    if(!$quote->getCustomerIsGuest()&&$quote->getCustomerId()){
      $this->debug("CONVERT TO QUOTE CUSTOMER: ".$quote->getId());
      $orderId = $this->customer_payment->savePaymentInformationAndPlaceOrder(
        $quote->getId(),
        $this->payment_method
      );
    }else{
      $this->debug("CONVERT TO QUOTE GUEST: ".$quote->getId());
      $orderId = $this->guest_payment->savePaymentInformationAndPlaceOrder(
        $cartId,
        $quote->getCustomerEmail(),
        $this->payment_method
      );
    }

    if ($response->isCreatePendingOrder()) {
      if ($orderId != null) {
        $order = $this->order_repository->get($orderId);
        $this->setOrderStatus(
          $order,
          \Magento\Sales\Model\Order::STATE_NEW,
          \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
          "",
          false
        );
      }
    }else{
      if ($orderId != null) {
        $order = $this->order_repository->get($orderId);
        $this->generateInvoice($order);
        $isSuspectedFraud = $this->isSuspectedFraud($order, $response);
        if(!$isSuspectedFraud){
          $this->setOrderStatus(
            $order,
            \Magento\Sales\Model\Order::STATE_PROCESSING,
            \Magento\Sales\Model\Order::STATE_PROCESSING,
            "",
            false
          );
          //Send email
          $this->email_order_sender->send($order, true);
        }
      }
    }
    $this->debug("CONVERTED QUOTE: ".$quote->getId()." TO ORDER ".$orderId);
    return $orderId;
  }

  public function isSuspectedFraud($order, $response){
    $responseAmount = $response->getPaidAmount();
    if(!$responseAmount){
      $this->setOrderStatus(
        $order,
        \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
        \Magento\Sales\Model\Order::STATUS_FRAUD,
        __('Suspected fraud, cannot find paid amount in respose'),
        false
      );
      return true;
    }
    if($responseAmount != $order->getGrandTotal()){
      $this->setOrderStatus(
        $order,
        \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
        \Magento\Sales\Model\Order::STATUS_FRAUD,
        __('Suspected fraud, captured %1 but order value is %2', [$responseAmount , $order->getGrandTotal()]),
        false
      );
      return true;
    }
    return false;
  }

  /*
  * Helper Function to Set Order State
  */
  public function setOrderStatus(&$order, $state, $status, $comment, $isCustomerNotified ) {
      $order->setState($state);// Set the new state
      $order->addStatusToHistory($status,$comment,$isCustomerNotified);// Set a histroy status
      $order->save();
  }

  public function generateInvoice($order){
    $invoice = $this->invoice_service->prepareInvoice($order);
    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
    $invoice->register();
    $invoice->getOrder()->setIsInProcess(true);

    $transactionSave = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
    $transactionSave->save();
  }

  public function invalidateCartCookie(){
    $metadata = $this->cookie_metadata_factory
      ->createPublicCookieMetadata()
      ->setPath('/');
    $sectiondata = json_decode($this->cookie_manager->getCookie('section_data_ids'));
    $sectiondata->cart += 1000;
    $this->cookie_manager->setPublicCookie(
        'section_data_ids',
        json_encode($sectiondata),
        $metadata
    );
  }

  public function debug($msg){
    if($this->getConfig('payment/sipay_sipay/debug')!="0"){
      $this->logger->debug("[SIPAY DEBUG] ".$msg);
    }
  }

  public function critical($msg){
    $this->logger->critical("[SIPAY EXCEPTION] ".$msg);
  }

  public function getConfig($path){
    return $this->config->getValue($path);
  }

  public function getBackendUrl(){
    return $this->url_builder->getUrl('sipay/backend/index');
  }

  public function getUrl($path){
    return $this->url_builder->getUrl($path);
  }

  public function getFlattenJson($jsonData){
    $flatten = new \Sarhan\Flatten\Flatten(".");
    return $flatten->flattenToArray($jsonData);
  }

  //NEW EXPRESS CHECKOUT

  public function setAddressAndCollectRates($response, $quote){
    $response_address  = $response->getAddress();
    $response_customer = $response->getCustomerData();
    if($response_address /*&& $response_customer*/){
      $shipping_address         = $quote->getShippingAddress();
      $billing_address          = $quote->getBillingAddress();
      $response_billing_address = $response->hasBillingAddress() ? $response->getBillingAddress() : $response_address;

      $this->setAddressInMagentoAddress($response_address,         $response_customer, $shipping_address);
      $this->setAddressInMagentoAddress($response_billing_address, $response_customer, $billing_address);
      $shipping_address->save();
      $billing_address->save();
      if($response->hasCustomerData()){
        $quote->setCustomerEmail($response_customer["email"]);
      } 
      $shipping_methods = $this->shipping_method_management->estimateByExtendedAddress($quote->getId(), $shipping_address);
      if(count($shipping_methods) > 0){
        $shipping_method_code   = null;
        $shipping_carrier_code  = null; 
        foreach($shipping_methods as $shipping_method){
          $shipping_method_code  = $shipping_method->getMethodCode();
          $shipping_carrier_code = $shipping_method->getCarrierCode();
          $this->shipping_rate->setCode($shipping_method_code);
          break;
        }
        $shipping_address->setCollectShippingRates(true)
        ->collectShippingRates()
        ->setShippingMethod($shipping_method_code);

        $quote->getShippingAddress()->addShippingRate($this->shipping_rate);
        $shippingInformation = $this->shipping_information_factory->create();
        $shippingInformation
          //->setBillingAddress($shippingAddress)
          ->setShippingAddress($shipping_address)
          ->setShippingCarrierCode($shipping_carrier_code)
          ->setShippingMethodCode($shipping_method_code);

        $this->shipping_information_management->saveAddressInformation($quote->getId(), $shippingInformation);
        $quote->getPayment()->setMethod(\Sipay\Sipay\Helper\Constants::PAYMENT_METHOD_CODE);
        if($response->hasCustomerdata()){
          $this->quote_validator->validateBeforeSubmit($quote);
        }
        $quote->collectTotals();
        $this->quote_repository->save($quote);
        return false;
      }else{
        if($quote->isVirtual()){
          return false;
        }else{
          return "No shipping methods available";
        }
      }
    }
  }

  private function setAddressInMagentoAddress($address, $customer, &$magento_address){
    if($address && $magento_address){
      if($customer){
        (array_key_exists("email", $customer) && $customer["email"] && $customer["email"] != "") ? $magento_address->setEmail($customer["email"]) : null;
      }
      if(array_key_exists("name", $address) && $address["name"] && $address["name"] != ""){
        $magento_address->setFirstName($address["name"]);
      }else if($customer && array_key_exists("name", $customer) && $customer["name"] && $customer["name"] != ""){
        $magento_address->setFirstName($customer["name"]);
      }
      $magento_address->setLastName("-");

      if(array_key_exists("address", $address)){
          $concat_street = "";
          if(array_key_exists(0, $address["address"]) && $address["address"][0] != ""){
            $concat_street = $address["address"][0];
          } 
          if(array_key_exists(1, $address["address"]) && $address["address"][1] != ""){
            $concat_street = $concat_street . " " . $address["address"][1];
          }
          if(array_key_exists(2, $address["address"]) && $address["address"][2] != ""){
            $concat_street = $concat_street . " " . $address["address"][2];
          }
          if($concat_street != ""){
            $magento_address->setStreet($concat_street); 
          }   
      }
      (array_key_exists("city", $address) && $address["city"] && $address["city"] != "") ? $magento_address->setCity($address["city"]) : null;
      (array_key_exists("country_code", $address) && $address["country_code"] && $address["country_code"] != "") ? $magento_address->setCountryId($address["country_code"]) : null;
      if(array_key_exists("zip", $address) && $address["zip"] && $address["zip"] != ""){
        if($address["country_code"] == 'ES'){
          $magento_address->setRegionId($this->getRegionIdByPostcode($address["zip"]));
        }else{
          $magento_address->setRegionId("");
          $magento_address->setRegion("");
        }
      }else{
        $magento_address->setRegionId("");
        $magento_address->setRegion("");
      }
      // (array_key_exists("zip", $address) && $address["zip"] && $address["zip"] != "") ? $magento_address->setRegionId($address["country_code"] == 'ES' ? $this->getRegionIdByPostcode($address["zip"]) : "") : $magento_address->setRegionId("");
      (array_key_exists("phone", $address) && $address["phone"] && $address["phone"] != "") ? $magento_address->setTelephone($address["phone"]) : $magento_address->setTelephone("600000000");
      (array_key_exists("zip", $address) && $address["zip"] && $address["zip"] != "") ? $magento_address->setPostCode($address["zip"]) : null;
    }
  }

  private function getRegionIdByPostcode($postcode){
    $first_two_chars = substr($postcode, 0 , 2);
    if(array_key_exists($first_two_chars, \Sipay\Sipay\Helper\Constants::POSTCODE_REGIONID_SPAIN)){
      return \Sipay\Sipay\Helper\Constants::POSTCODE_REGIONID_SPAIN[$first_two_chars];
    }
    return null;
  }
  

  public function getPaypalItemsInfo($quote){
    
    $totals     = array(
      "total"     => $quote->getGrandTotal(),
      "shipping"  => $quote->getShippingAddress()->getShippingAmount(),
      "tax"       => $quote->isVirtual() ? $quote->getBillingAddress()->getTaxAmount() : $quote->getShippingAddress()->getTaxAmount()
    );

    $cart_items = [];
    foreach($quote->getAllVisibleItems() as $item){
      $cart_items[] = array(
        "name"       => $item->getName(),
        "sku"        => $item->getSku(),
        "qty"        => $item->getQty(),
        "unit_price" => $item->getPrice(),
        "unit_tax"   => ($item->getTaxPercent()/100) * $item->getPrice(),
        "is_digital" => $item->getProductType() == \Magento\CatalogImportExport\Model\Import\Product\Type\Virtual::TYPE_VIRTUAL_PRODUCT
      );
    }
    $res = \PWall\Request::buildPaypalCartInfo($quote->getQuoteCurrencyCode(),$cart_items,$totals);
    return $res;
    
  }

  public function setPDS2Params(&$request, $quote, $customer){
    $tra_enabled  = boolval($this->getConfig('payment/sipay_sipay/sipay_sipay_psd2/tra'));
    $tra_value    = floatval($this->getConfig('payment/sipay_sipay/sipay_sipay_psd2/tra_value'));
    $lwv_enabled  = boolval($this->getConfig('payment/sipay_sipay/sipay_sipay_psd2/lwv'));
    $lwv_value    = floatval($this->getConfig('payment/sipay_sipay/sipay_sipay_psd2/lwv_value'));

    $cart_total   = floatval($quote->getGrandTotal());

    $billing_address  = $quote->getBillingAddress();
    $shipping_address = $quote->getShippingAddress();

    $is_guest = $customer->getId() == null;

    $customer_data = [];
    $customer_data["account_modification_date"] = $is_guest ? "" : $customer->getUpdatedAt();            
    $customer_data["account_creation_date"]     = $is_guest ? "" : $customer->getCreatedAt();              
    $customer_data["account_purchase_number"]   = strval($this->getOrdersByCustomer($quote->getCustomerEmail()));                   
    $customer_data["account_age_date"]          = $is_guest ? "" : $customer->getDob();                           
    $customer_data["billing_city"]              = $billing_address->getCity();
    $customer_data["billing_country"]           = $billing_address->getCountry();  
    $customer_data["billing_address_1"]         = $billing_address->getStreet()[0];    
    $customer_data["billing_address_2"]         = array_key_exists(1, $billing_address->getStreet()) ? $billing_address->getStreet()[1] : "";    
    $customer_data["billing_address_3"]         = array_key_exists(2, $billing_address->getStreet()) ? $billing_address->getStreet()[2] : "";    
    $customer_data["billing_postcode"]          = $billing_address->getPostCode();
    if($quote->isVirtual()){
      $customer_data["delivery_email_address"]    = $quote->getCustomerEmail();
    }                                                         
    $customer_data["shipping_city"]             = $shipping_address->getCity();
    $customer_data["shipping_country"]          = $shipping_address->getCountry();    
    $customer_data["shipping_address_1"]        = $shipping_address->getStreet()[0];      
    $customer_data["shipping_address_2"]        = array_key_exists(1, $shipping_address->getStreet()) ? $shipping_address->getStreet()[1] : "";     
    $customer_data["shipping_address_3"]        = array_key_exists(2, $shipping_address->getStreet()) ? $shipping_address->getStreet()[2] : "";     
    $customer_data["shipping_postcode"]         = $shipping_address->getPostCode();

    $request->setPSD2Info($tra_enabled, $tra_value, $lwv_enabled, $lwv_value, $cart_total, $customer_data);
  }

  private function getOrdersByCustomer($email){
    return $this->order_collection->create()->addFieldToSelect('entity_id')->addFieldToFilter('customer_email', ['eq' => $email])->count();
  }

}
