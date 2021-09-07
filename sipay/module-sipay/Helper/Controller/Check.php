<?php

namespace Sipay\Sipay\Helper\Controller;

class Check
{

  protected $result_factory;
  protected $config;
  protected $logger;
  protected $checkout;
  protected $request;
  protected $helper;
  protected $quote_repository;

  public function __construct(
      \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
      \Magento\Checkout\Helper\Data                    $checkoutHelper,
      \Magento\Quote\Api\CartRepositoryInterface       $quoteRepository,
      \Sipay\Sipay\Helper\Data                        $helper
  )
  {
    $this->result_factory    = $resultJsonFactory;
    $this->helper            = $helper;
    $this->checkout          = $checkoutHelper;
    $this->quote_repository  = $quoteRepository;
  }

  public function execute($request)
  {
    $this->helper->debug("ON CHECK QUOTE: " . json_encode($request->getParams()) );
    $result       = $this->result_factory->create();
    $quote        = $this->checkout->getQuote();
    if(!$quote->getId()||!$quote->getIsActive()){
      $result->setData(["success" => "false"]);
      return $result;
    }
    if($request->getParam("isLoggedIn") == "false"){
      $this->helper->debug("NOT LOGGED IN -> UPDATE QUOTE ".$quote->getId());
      $quote->setCustomerId(null);
      $quote->setCustomerEmail($request->getParam("email"));
      $quote->setCustomerIsGuest(true);
      $this->quote_repository->save($quote);
    }

    $result->setData(["success" => "true"]);
    return $result;
  }
}
