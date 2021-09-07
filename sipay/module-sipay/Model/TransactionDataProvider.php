<?php
namespace Sipay\Sipay\Model;

use Sipay\Sipay\Model\ResourceModel\Transaction\Collection;
use Sipay\Sipay\Model\ResourceModel\Transaction\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;

class TransactionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $transactionCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $transactionCollectionFactory,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $transactionCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
        $this->request = $request;

    }

    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */

    public function getData()
    {
      if (isset($this->loadedData)) {
        return $this->loadedData;
      }

      $itemId = $this->request->getParam('entity_id');

      if ( !empty($itemId) ) {
        $items = $this->collection->getItems();
        foreach ($items as $item) {
          $this->loadedData[$item->getId()] = $item->getData();
        }

        return $this->loadedData;
      }else{
        return [];
      }

    }

}
