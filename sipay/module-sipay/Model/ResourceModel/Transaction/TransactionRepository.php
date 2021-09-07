<?php

namespace Sipay\Sipay\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Sipay\Sipay\Api\Data\TransactionInterface;
use Sipay\Sipay\Api\TransactionRepositoryInterface;
use Sipay\Sipay\Model\ResourceModel\Transaction\Collection as TransactionCollectionFactory;
use Sipay\Sipay\Model\ResourceModel\Transaction\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
  /**
   * @var Transaction
   */
  private $transactionFactory;

  /**
   * @var TransactionCollectionFactory
   */
  private $transactionCollectionFactory;

  public function __construct(
      TransactionFactory $transactionFactory,
      TransactionCollectionFactory $transactionCollectionFactory
  ) {
      $this->transactionFactory = $transactionFactory;
      $this->transactionCollectionFactory = $transactionCollectionFactory;
  }

  public function getById($entity_id)
  {
      $transaction = $this->transactionFactory->create();
      $transaction->getResource()->load($transaction, $entity_id);
      if (! $transaction->getId()) {
          throw new NoSuchEntityException(__('Unable to find transaction with ID "%1"', $entity_id));
      }
      return $transaction;
  }

  public function save(TransactionInterface $transaction)
  {
      $transaction->getResource()->save($transaction);
      return $transaction;
  }

  public function delete(TransactionInterface $transaction)
  {
      $transaction->getResource()->delete($transaction);
  }
}
