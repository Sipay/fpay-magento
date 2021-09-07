<?php

namespace Sipay\Sipay\Controller\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;


class Index extends \Magento\Framework\App\Action\Action
{

  protected $quote_helper;

  public function __construct(
      \Magento\Framework\App\Action\Context            $context,
      \Sipay\Sipay\Helper\Controller\Quote            $quoteHelper
  )
  {
    $this->quote_helper  = $quoteHelper;
    parent::__construct($context);
  }

  public function execute()
  {
    return $this->quote_helper->getExpressCheckoutInfo();
  }
}
