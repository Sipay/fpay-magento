<?php

namespace Sipay\Sipay\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TransactionInterface extends ExtensibleDataInterface
{
  /**
   * @return int
   */
  public function getId();

  /**
   * @param int $entity_id
   * @return void
   */
  public function setQId($id);

  /**
   * @return int
   */
  public function getQuoteId();

  /**
   * @param int $quote_id
   * @return void
   */
  public function setQuoteId($quote_id);

  /**
   * @return int
   */
  public function getRequest();

  /**
   * @param int $request
   * @return void
   */
  public function setRequest($request);

  /**
   * @return string
   */
  public function getResponse();

  /**
   * @param string $response
   * @return void
   */
  public function setResponse($response);

  /**
   * @return string
   */
  public function getCreationTime();

  /**
   * @param string $creation_time
   * @return void
   */
  public function setCreationTime($creation_time);

  /**
   * @return string
   */
  public function getUpdateTime();

  /**
   * @param string $update_time
   * @return void
   */
  public function setUpdateTime($update_time);

}
