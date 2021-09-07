<?php

namespace Sipay\Sipay\Block\Adminhtml\Payment;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

class Info extends ConfigurableInfo
{
  protected $helper;
  protected $registry;

  public function __construct(
      \Magento\Framework\View\Element\Template\Context  $context,
      \Magento\Payment\Gateway\ConfigInterface          $config,
      \Magento\Framework\Registry                       $registry,
      \Sipay\Sipay\Helper\Data                          $helper,
      \Sipay\Sipay\Model\PaymentMethod                  $sipayPaymentMethod,
      array $data = []
  ) {
      parent::__construct($context, $config, $data);
      $this->helper                 = $helper;
      $this->registry               = $registry;
      $this->sipay_payment_method   = $sipayPaymentMethod; 
  }

  public function getFlattenedData(){
    $order = $this->registry->registry('current_order');
    $payment_method = $order->getPayment();
    if($payment_method->getMethodInstance()->getCode() == $this->sipay_payment_method->getCode()){
      return $this->helper->getFlattenJson(
        json_decode($order->getPayment()->getAdditionalInformation()["response"][0], true)
      );
    }
  }
}
