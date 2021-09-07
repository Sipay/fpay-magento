<?php

namespace Sipay\Sipay\Controller\Payment;

class Index extends \Magento\Framework\App\Action\Action
{

  public function __construct(
    \Magento\Framework\App\Action\Context       $context,
    \Magento\Framework\View\Result\PageFactory  $resultPageFactory, 
    \Magento\Checkout\Model\Session             $session,
    \Magento\Store\Model\StoreManagerInterface  $storeManager,
    \Magento\Framework\Controller\ResultFactory $resultFactory,
    \Magento\Framework\UrlInterface             $urlBuilder,
    \Sipay\Sipay\Helper\Data                   $helper
  )
  {
    $this->_session           = $session;
    $this->resultPageFactory  = $resultPageFactory;
    $this->_storeManager      = $storeManager;
    $this->request            = $context->getRequest();
    $this->result_factory     = $resultFactory;
    $this->url_builder        = $urlBuilder;
    $this->helper             = $helper;
    return parent::__construct($context);
  }

  public function execute(){
    if(!$this->request->getParam("request_id") || !$this->request->getParam("method")){
      $resultRedirect = $this->result_factory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
      $resultRedirect->setUrl($this->url_builder->getUrl("noroute"));
      return $resultRedirect;
    }
    $resultPage                 = $this->resultPageFactory->create();
    $page_title = $this->helper->getConfig('payment/sipay_sipay/payment_review_title');
    $resultPage->getConfig()->getTitle()->set(__(!$page_title || $page_title == "" ? "Sipay payment review" : $page_title));
    return $resultPage;
  }

}
