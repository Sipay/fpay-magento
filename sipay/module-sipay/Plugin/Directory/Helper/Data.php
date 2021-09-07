<?php

namespace Sipay\Sipay\Plugin\Directory\Helper;

class Data{

  protected $registry;
  protected $sipay_constants;

  public function __construct(
    \Magento\Framework\Registry     $magentoRegistry,
    \Sipay\Sipay\Helper\Constants  $sipayConstants
  )
  {
    $this->registry           = $magentoRegistry;
    $this->sipay_constants    = $sipayConstants;
  }


  public function aroundIsRegionRequired($subject,$proceed,$countryId)
  {

    if(!$this->registry->registry($this->sipay_constants::SIPAY_EC_NOREGION_ID)){
      return $proceed($countryId);
    }
    return false;
  }
}