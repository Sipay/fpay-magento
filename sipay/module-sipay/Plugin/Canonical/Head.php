<?php

namespace Sipay\Sipay\Plugin\Canonical;

/**
 * Head
 */
class Head{

  protected $helper_data;

  public function __construct(
    \Sipay\Sipay\Helper\Data $helperData
  )
  {
    $this->helper_data = $helperData;
  }


  public function aroundRenderMetadata($subject,$proceed)
  {
    $result = $proceed();
    // Add SDK bundle js
    $sdk_js = null;
    if (array_key_exists($this->helper_data->getConfig('payment/sipay_sipay/environment'), \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS)) {
      $sdk_js = \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS[$this->helper_data->getConfig('payment/sipay_sipay/environment')] . 'pwall_sdk/pwall_sdk.bundle.js';
    }  
    $url = '
    <script type="text/javascript" src="'.$sdk_js.'"></script>'.PHP_EOL.
    '<script type="text/javascript" src="'. \Sipay\Sipay\Helper\Constants::SDK_JS_URL.'"></script>';
    return $result.$url;
  }
}