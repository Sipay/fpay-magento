<?php

namespace Sipay\Sipay\Block\Payment;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class PaymentReview extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context    $context,
        \Sipay\Sipay\Helper\Data                            $helperData,
        \Magento\Framework\Registry                         $registry,
        \Magento\Checkout\Model\Session                     $checkoutSession,
        \Magento\Customer\Model\Session                     $customerSession,
        \Magento\Quote\Api\CartRepositoryInterface          $quoteRepository,

        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper_data                          = $helperData;
        $this->registry                             = $registry;
        $this->checkout_session                     = $checkoutSession;
        $this->customer_session                     = $customerSession;
        $this->quote_repository                     = $quoteRepository;
    }

    public function getEnvironment(){
        return $this->helper_data->getConfig('payment/sipay_sipay/environment');
    }

    public function getBackendUrl(){
        return $this->helper_data->getBackendUrl();
    }

    public function getSdkJsUrl(){
      return \Sipay\Sipay\Helper\Constants::SDK_JS_URL;
    }

    public function getSdkUrl()
    {
        if (array_key_exists($this->helper_data->getConfig('payment/sipay_sipay/environment'), \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS)) {
            return \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS[$this->helper_data->getConfig('payment/sipay_sipay/environment')] . "pwall_sdk/pwall_sdk.bundle.js";
        }
    }

    public function getCssUrl()
    {
        if(array_key_exists($this->helper_data->getConfig('payment/sipay_sipay/environment'), \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS)){
            return \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS[$this->helper_data->getConfig('payment/sipay_sipay/environment')] . "pwall_app/css/app.css";      
        }         
    }

    public function getQuote()
    {
        $quote = $this->checkout_session->getQuote();
        if(!$quote || !$quote->getId()){
            $quoteId = $this->customer_session->getSipayQuoteId();
            $quote = $this->quote_repository->get($quoteId);
        }
        return $quote;
    }

    public function getAmountFromQuote(){
        $quote = $this->getQuote();
        return $quote->getGrandTotal();
    }

    public function getCurrencyCodeFromQuote(){
        $quote = $this->getQuote();
        return $quote->getQuoteCurrencyCode();
    }

    public function getErrorRedirectUrl(){
        return $this->customer_session->getSipayErrorRedirectUrl();
    } 

    public function getCustomerId(){
        if($this->customer_session->isLoggedIn()){
            return $this->customer_session->getCustomer()->getId();
        }
        return 0;
    }

}
