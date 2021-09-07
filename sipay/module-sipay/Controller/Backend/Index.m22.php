<?php

namespace Sipay\Sipay\Controller\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;


class Index extends \Magento\Framework\App\Action\Action
{

  protected $result_factory;
  protected $request;
  protected $helper;
  protected $controller_helper;
  protected $customer_session;


    public function __construct(
      \Magento\Framework\App\Action\Context                             $context,
      \Magento\Framework\Controller\Result\JsonFactory                  $resultJsonFactory,
      \Sipay\Sipay\Helper\Data                                          $helper,
      \Sipay\Sipay\Helper\Controller\Backend                            $controllerHelper
    )
    {
      $this->result_factory       = $resultJsonFactory;
      $this->request              = $context->getRequest();
      $this->helper               = $helper;
      $this->controller_helper    = $controllerHelper;


      parent::__construct($context);
    }

    public function execute(){
      $jsonRequest  = $this->helper->parseJSONRequest($this->request->getContent());
      $redirect_url = $this->_redirect->getRefererUrl();
      return $this->controller_helper->executeByMethod($jsonRequest, $redirect_url);
    }
}
