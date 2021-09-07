<?php

namespace Sipay\Sipay\Model;

class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = \Sipay\Sipay\Helper\Constants::PAYMENT_METHOD_CODE;

    protected $_isInitializeNeeded = true;

    /**
     * Get config payment action, do nothing if status is pending
     *
     * @return string|null
     */
    public function getConfigPaymentAction()
    {
      return ($this->getConfigData('order_status') == 'pending') ? null : parent::getConfigPaymentAction();
    }

    // Fixes https://github.com/magento/magento2/issues/5413 in Magento 2.1
    public function setId($code) { }
    public function getId() { return $this::$code; }
}
