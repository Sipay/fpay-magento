<?php

namespace Sipay\Sipay\Model\ResourceModel\Transaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Sipay\Sipay\Model\Transaction', 'Sipay\Sipay\Model\ResourceModel\Transaction');
    }

}
