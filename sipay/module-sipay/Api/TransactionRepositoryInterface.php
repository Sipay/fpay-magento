<?php

namespace Sipay\Sipay\Api;

use Sipay\Sipay\Api\Data\TransactionInterface;

interface TransactionRepositoryInterface
{
    /**
     * @param int $entity_id
     * @return \Sipay\Sipay\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entity_id);

    /**
     * @param \Sipay\Sipay\Api\Data\TransactionInterface $transaction
     * @return \Sipay\Sipay\Api\Data\TransactionInterface
     */
    public function save(TransactionInterface $transaction);

    /**
     * @param \Sipay\Sipay\Api\Data\TransactionInterface $transaction
     * @return void
     */
    public function delete(TransactionInterface $transaction);

}
