<?php

namespace Sipay\Sipay\Block\ExpressCheckout;

class Button extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context    $context,
        \Sipay\Sipay\Helper\Data                            $helperData,
        \Magento\Framework\Registry                         $registry,
        \Magento\Checkout\Helper\Data                       $checkoutHelper,
        \Magento\Customer\Model\Session                     $customerSession,
        \Magento\Theme\Block\Html\Header\Logo               $magentoLogo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper_data      = $helperData;
        $this->registry         = $registry;
        $this->checkout         = $checkoutHelper;
        $this->customer_session = $customerSession;
        $this->magento_logo     = $magentoLogo;
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

    /**
     * Check Is Block enabled
     * @return bool
     */
    public function isEnabled($location)
    {
        switch($location){
            case "product_view":
                return $this->helper_data->getConfig('payment/sipay_osc/sipay_osc_product_page/active');
            case "checkout_cart":
                return $this->helper_data->getConfig('payment/sipay_osc/sipay_osc_cart/active');
            case "minicart":
                return $this->helper_data->getConfig('payment/sipay_osc/sipay_osc_minicart/active');
            default:
                return "0";
        }   
    }

    public function getContainerElement($location){
        $config_path = $this->getConfigPath($location);
        if ($this->helper_data->getConfig($config_path . "/position_mode") == "1") {
            return $this->helper_data->getConfig($config_path . "/position_selector");
        }
        return null;
    }

    public function getContainerStyle($location){
        $config = [];
        $config_path = $this->getConfigPath($location);

        if($this->helper_data->getConfig($config_path . "/container_active") == "1"){
            $config["color"]                    = $this->helper_data->getConfig($config_path . "/container_border_color");
            $config["custom_color"]             = $this->helper_data->getConfig($config_path . "/container_border_custom_color");
            $config["header_title"]             = $this->helper_data->getConfig($config_path . "/container_header_title");
            $config["header_title_typo"]        = $this->helper_data->getConfig($config_path . "/container_header_title_typo");
            $config["descriptive_text"]         = $this->helper_data->getConfig($config_path . "/container_descriptive_text");
            $config["descriptive_text_typo"]    = $this->helper_data->getConfig($config_path . "/container_descriptive_text_typo");
        }        

        return json_encode($config);
    }

    public function getPositionConfig($location){
        $config = [];
        $config_path = $this->getConfigPath($location);

        if($this->helper_data->getConfig($config_path . "/position_mode") == "1"){
            $config["position_selector"]    = $this->helper_data->getConfig($config_path . "/position_selector");
            $config["insertion"]            = $this->helper_data->getConfig($config_path . "/position_insertion");
        }
        
        return json_encode($config);
    }

    public function getPositionStyleConfig($location){
        $config = [];
        $config_path = $this->getConfigPath($location);

        if ($this->helper_data->getConfig($config_path . "/position_mode") == "1") {
            $positionStyleConfig    = $this->helper_data->getConfig($config_path . "/position_style");
            $config                 = $positionStyleConfig != null && $positionStyleConfig != "" ? preg_replace('/\r\n|\r|\n/', '', $this->helper_data->getConfig($config_path . "/position_style")) : null;
        }
        if(!$config){
            return json_encode($config);
        }

        return $config;
    }

    private function getConfigPath($location){
        if($location == "product_view"){
            return "payment/sipay_osc/sipay_osc_product_page";
        }else if($location == "checkout_cart"){
            return "payment/sipay_osc/sipay_osc_cart";
        }else{
            return "payment/sipay_osc/sipay_osc_minicart";
        }
    }

    public function getQuoteInfoUrl(){
        return $this->helper_data->getUrl("sipay/quote");
    }

    public function getLogoSrc(){
        return $this->magento_logo->getLogoSrc();
    }

    
}
