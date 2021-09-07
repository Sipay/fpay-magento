<?php

namespace Sipay\Sipay\Helper\Transaction;

class Data
{

  protected $transaction_factory;
  protected $transaction_repository;

  public function __construct(
    \Sipay\Sipay\Model\TransactionFactory          $transactionFactory,
    \Sipay\Sipay\Model\ResourceModel\Transaction   $transactionRepository
  ){
    $this->transaction_factory    = $transactionFactory;
    $this->transaction_repository = $transactionRepository;
  }

  public function createTransaction($quote_id, $request, $response){
    $transaction = $this->transaction_factory->create();
    $transaction->setQuoteId($quote_id);
    $transaction->setRequest(json_encode($request));
    $transaction->setResponse(json_encode($response));
    $this->transaction_repository->save($transaction);
  }


}
