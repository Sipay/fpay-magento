<?php
namespace Sipay\Sipay\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
  /**
   * {@inheritdoc}
   */
  public function upgrade(
    \Magento\Framework\Setup\SchemaSetupInterface $setup,
    \Magento\Framework\Setup\ModuleContextInterface $context
  ){
    $installer = $setup;

    $installer->startSetup();

    if (version_compare($context->getVersion(), '1.0.1', '<')) {
      $table = $setup->getConnection()->newTable(
      $setup->getTable('sipay_sipay_transactions'))
       ->addColumn(
          'entity_id',
          \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
          null,
          ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
          'Entity Id for transaction'
      )->addColumn(
          'quote_id',
          \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
          null,
          ['nullable' => false],
          'Quote Id'
      )->addColumn(
          'request',
          \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
          1000,
          [],
          'Transaction request'
      )->addColumn(
          'response',
          \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
          1000,
          [],
          'Transaction response'
      )->addColumn(
          'creation_time',
          \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
          null,
          ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
          'Creation Time'
      )->addColumn(
          'update_time',
          \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
          null,
          ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
          'Update Time'
      )->setComment(
          'Sipay Transactions'
      );
      $setup->getConnection()->createTable($table);
    }

    $installer->endSetup();

  }
}
