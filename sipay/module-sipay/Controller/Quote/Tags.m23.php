<?php

namespace Sipay\Sipay\Controller\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;


class Tags extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\CsrfAwareActionInterface
{

  protected $quote_helper;

  /**
   * @inheritDoc
   */
  public function createCsrfValidationException(
      RequestInterface $request
  ): ?InvalidRequestException {
      return null;
  }

  /**
   * @inheritDoc
   */
  public function validateForCsrf(RequestInterface $request): ?bool
  {
      return true;
  }

  public function __construct(
    \Magento\Framework\App\Action\Context            $context,
    \Sipay\Sipay\Helper\Controller\Quote            $quoteHelper
  ) {
    $this->quote_helper  = $quoteHelper;
    parent::__construct($context);
  }

  public function execute()
  {
    return $this->quote_helper->getTagsFromQuote();
  }
}
