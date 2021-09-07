<?php

namespace Sipay\Sipay\Controller\Check;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;


class Index extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\CsrfAwareActionInterface
{

  protected $result_factory;
  protected $config;
  protected $logger;
  protected $checkout;
  protected $request;
  protected $helper;
  protected $quote_repository;

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
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Sipay\Sipay\Helper\Controller\Check            $checkHelper
  )
  {
    $this->result_factory    = $resultJsonFactory;
    $this->request           = $context->getRequest();
    $this->check_helper      = $checkHelper;

    parent::__construct($context);
  }

  public function execute()
  {
    return $this->check_helper->execute($this->request);
  }
}
