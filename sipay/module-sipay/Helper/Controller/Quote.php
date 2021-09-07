<?php

namespace Sipay\Sipay\Helper\Controller;

class Quote
{

  public function __construct(
    \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory,
    \Magento\Checkout\Helper\Data                       $checkoutHelper,
    \Magento\Customer\Model\Session                     $customerSession,
    \Magento\Quote\Model\QuoteManagement                $quoteManagement,
    \Magento\Quote\Api\CartRepositoryInterface          $quoteRepository
  )
  {
    $this->result_factory   = $resultJsonFactory;
    $this->checkout         = $checkoutHelper;
    $this->customer_session = $customerSession;
    $this->quote_management = $quoteManagement;
    $this->quote_repository = $quoteRepository;
  }

  public function getExpressCheckoutInfo(){
    $response = [];
    $response["tags"]       = \Sipay\Sipay\Helper\Constants::EXPRESS_CHECKOUT_TAG;  //Fixed tag for express checkout
    $response["currency"]   = $this->getCurrencyCodeFromQuote();
    $response["amount"]     = $this->getAmountFromQuote();
    $response["group_id"]   = $this->getCustomerId();
    $result                 = $this->result_factory->create();
    $result->setData($response);
    return $result;
  }

  /**
   * Get Quote
   * @return \Magento\Quote\Model\Quote
   */
  public function getQuote()
  {
    $quote = $this->checkout->getCheckout()->getQuote();
    $quoteId = null;
    if (!$quote->getId()) {
      if ($this->customer_session->isLoggedIn()) {
        $quoteId = $this->quote_management->createEmptyCartForCustomer($this->getCustomerId());
      }else{
        $quoteId = $this->quote_management->createEmptyCart();
      }
      return $this->quote_repository->get($quoteId);
    }

    return $quote;
  }

  public function getTagsFromQuote()
  {
    $quote = $this->getQuote();
    $has_virtual   = false;
    $has_novirtual = false;

    if ($quote->getItems()) {
      foreach ($quote->getAllItems() as $item) {
        if ($item->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL) {
          $has_virtual = true;
        } else {
          $has_novirtual = true;
        }
      }
    }

    if ($has_virtual && $has_novirtual) {
      return \Sipay\Sipay\Helper\Constants::CHECKOUT_TAG_BOTH;
    } else if ($has_virtual) {
      return \Sipay\Sipay\Helper\Constants::CHECKOUT_TAG_VIRTUAL;
    } else {
      return \Sipay\Sipay\Helper\Constants::CHECKOUT_TAG_NOVIRTUAL;
    }
  }

  public function getAmountFromQuote()
  {
    $quote = $this->getQuote();
    return $quote->getGrandTotal();
  }

  public function getCurrencyCodeFromQuote()
  {
    $quote = $this->getQuote();
    return $quote->getQuoteCurrencyCode();
  }

  public function getCustomerId()
  {
    if ($this->customer_session->isLoggedIn()) {
      return $this->customer_session->getCustomer()->getId();
    }
    return 0;
  }

}