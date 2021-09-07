<?php

namespace Sipay\Sipay\Model\Config;

class Provider implements \Magento\Checkout\Model\ConfigProviderInterface
{
  const CODE = \Sipay\Sipay\Helper\Constants::PAYMENT_METHOD_CODE;

  protected $config;
  protected $url_builder;

  public function __construct(\Magento\Framework\App\Helper\Context $context)
  {
    $this->config       = $context->getScopeConfig();
    $this->url_builder  = $context->getUrlBuilder();
  }

  public function getConfig()
  {
    return [
        'payment' => [
          self::CODE => [
            'environment'        => $this->config->getValue('payment/sipay_sipay/environment'),
            'environment_url'   => \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS[$this->config->getValue('payment/sipay_sipay/environment')],
            'debug'             => $this->config->getValue('payment/sipay_sipay/debug'),
            'backend_url'       => $this->url_builder->getUrl('sipay/backend/index'),
            'check_url'         => $this->url_builder->getUrl('sipay/check'),
            'quoteTags'         => $this->url_builder->getUrl('sipay/quote/tags')
          ]
        ]
      ];
  }
}
