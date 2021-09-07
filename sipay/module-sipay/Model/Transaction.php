<?php

namespace Sipay\Sipay\Model;

class Transaction extends \Magento\Framework\Model\AbstractModel implements \Sipay\Sipay\Api\Data\TransactionInterface
{
  public function __construct(
    \Magento\Framework\Model\Context $context,
    \Magento\Framework\Registry $registry,
    \Sipay\Sipay\Model\ResourceModel\Transaction $resource,
    \Sipay\Sipay\Model\ResourceModel\Transaction\Collection $resourceCollection,
    array $data = []
  ){
    parent::__construct(
              $context,
              $registry,
              $resource,
              $resourceCollection,
              $data);
  }

  protected function _construct()
  {
    $this->_init('Sipay\Sipay\Model\ResourceModel\Transaction');
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->_getData('entity_id');
  }

  /**
   * @param int $entity_id
   * @return void
   */
  public function setQId($id){
    return $this->setData('entity_id',$id);
  }

  /**
   * @return int
   */
  public function getQuoteId()
  {
    return $this->_getData('quote_id');
  }

  /**
   * @param int $quote_id
   * @return void
   */
  public function setQuoteId($quote_id)
  {
    return $this->setData('quote_id',$quote_id);
  }

  /**
   * @return int
   */
  public function getRequest()
  {
    return $this->_getData('request');
  }

  /**
   * @param int $request
   * @return void
   */
  public function setRequest($request)
  {
    return $this->setData('request',$request);
  }

  /**
   * @return string
   */
  public function getResponse()
  {
    return $this->_getData('response');
  }

  /**
   * @param string $response
   * @return void
   */
  public function setResponse($response)
  {
    return $this->setData('response',$response);
  }

  /**
   * @return string
   */
  public function getCreationTime()
  {
    return $this->_getData('creation_time');
  }

  /**
   * @param string $creation_time
   * @return void
   */
  public function setCreationTime($creation_time)
  {
    return $this->setData('creation_time',$creation_time);
  }

  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->_getData('update_time');
  }

  /**
   * @param string $update_time
   * @return void
   */
  public function setUpdateTime($update_time)
  {
    return $this->setData('update_time',$update_time);
  }

  public function beforeSave()
  {
    parent::beforeSave();
    $this->setUpdatedAt(date('Y-m-d H:i:s'));
    return $this;
  }
}
