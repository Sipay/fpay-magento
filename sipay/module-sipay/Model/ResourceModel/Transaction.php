<?php
namespace Sipay\Sipay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sipay_sipay_transactions', 'entity_id');
    }
}
