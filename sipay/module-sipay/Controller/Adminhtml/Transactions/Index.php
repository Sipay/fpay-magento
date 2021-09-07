<?php
namespace Sipay\Sipay\Controller\Adminhtml\Transactions;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\Page;

class Index extends Action
{
    public function execute()
    {
      return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
