<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Quote\Api\Data\CartInterface;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface;
use Paazl\CheckoutWidget\Model\ResourceModel\Order\OrderReference;
use Paazl\CheckoutWidget\Model\ResourceModel\Quote\QuoteReference;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $currentVersion = $context->getVersion();

        if (version_compare($currentVersion, '1.0.1', '<')) {
            $this->createOrderTable($setup);
        }

        if (version_compare($currentVersion, '1.0.6', '<')) {
            $this->createQuoteTable($setup);
        }

        if (version_compare($currentVersion, '1.6.0', '<')) {
            $this->addInvalidColumnToOrderTable($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function createOrderTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(OrderReference::MAIN_TABLE);
        $connection = $setup->getConnection();
        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }
        if (!$connection->isTableExists($tableName)) {
            $table = $connection->newTable($tableName);
            $table
                ->addColumn(
                    OrderReferenceInterface::ENTITY_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    OrderReferenceInterface::ORDER_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Magento Order Id'
                )
                ->addColumn(
                    OrderReferenceInterface::EXT_SHIPPING_INFO,
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Customer selection of the Paazl delivery'
                )
                ->addColumn(
                    OrderReferenceInterface::EXT_SENT_AT,
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true, 'default' => null],
                    'When order has been synced with Paazl'
                )
                ->addIndex(
                    $setup->getIdxName($tableName, [OrderReferenceInterface::EXT_SENT_AT]),
                    [OrderReferenceInterface::EXT_SENT_AT]
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['order_id']),
                    ['order_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $setup->getFkName($tableName, 'order_id', $setup->getTable('sales_order'), 'entity_id'),
                    'order_id',
                    $setup->getTable('sales_order'),
                    'entity_id',
                    AdapterInterface::FK_ACTION_CASCADE
                );

            $connection->createTable($table);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function createQuoteTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(QuoteReference::MAIN_TABLE);
        $connection = $setup->getConnection();
        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }
        if (!$connection->isTableExists($tableName)) {
            $table = $connection->newTable($tableName);
            $table
                ->addColumn(
                    QuoteReferenceInterface::ENTITY_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    QuoteReferenceInterface::QUOTE_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Quote Id'
                )
                ->addColumn(
                    QuoteReferenceInterface::EXT_SHIPPING_INFO,
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true,],
                    'Shipping information'
                )
                ->addColumn(
                    QuoteReferenceInterface::TOKEN,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true,],
                    'Token'
                )
                ->addColumn(
                    QuoteReferenceInterface::TOKEN_EXPIRES_AT,
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true,],
                    'Token expires at'
                )
                ->addIndex(
                    $setup->getIdxName($tableName, [QuoteReferenceInterface::QUOTE_ID]),
                    [QuoteReferenceInterface::QUOTE_ID],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $setup->getFkName(
                        $tableName,
                        QuoteReferenceInterface::QUOTE_ID,
                        $setup->getTable('quote'),
                        CartInterface::KEY_ENTITY_ID
                    ),
                    QuoteReferenceInterface::QUOTE_ID,
                    $setup->getTable('quote'),
                    CartInterface::KEY_ENTITY_ID,
                    AdapterInterface::FK_ACTION_CASCADE
                );
            ;
            $connection->createTable($table);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addInvalidColumnToOrderTable($setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(OrderReference::MAIN_TABLE),
            OrderReferenceInterface::EXT_INVALID,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'Invalid'
            ]
        );
    }
}
