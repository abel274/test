<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Cms module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addStoreIdField($setup);
            $this->addTotalQtyFieldToStockItem($setup);
            $this->addShelfLocationFieldToStockItem($setup);
        }
        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->addWarehouseStoreViewTable($setup);
        }
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->addUpdatedTimeFieldStockItem($setup);
        }
        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            $this->addDeliveryDateToOrder($setup);
        }
        if (version_compare($context->getVersion(), '1.1.4', '<')) {
            $this->addIsAfterRestockDateToOrderItem($setup);
            $this->addReservedQtyToCatalogStockItem($setup);
        }
    }

    // add is_after_restock_date to sales_order
    protected function addIsAfterRestockDateToOrderItem(SchemaSetupInterface $setup) {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order_item'), 'is_after_restock_date')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'is_after_restock_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is After Restock Date'
                ]
            );
        }
    }

    // add delivery_date to sales_order
    protected function addDeliveryDateToOrder(SchemaSetupInterface $setup) {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order'), 'delivery_date')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'delivery_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Delivery Date'
                ]
            );
        }
    }

    // add reserved_qty to cataloginventory_stock_item
    protected function addReservedQtyToCatalogStockItem(SchemaSetupInterface $setup) {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('cataloginventory_stock_item'), 'reserved_qty')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cataloginventory_stock_item'),
                'reserved_qty',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Reserved Qty'
                ]
            );
        }
    }

    /**
     * Add store_id
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addStoreIdField(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_warehouse'), 'store_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_warehouse'),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'comment' => 'Store View Id'
                ]
            );
        }
        return $this;
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addTotalQtyFieldToStockItem(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('cataloginventory_stock_item'), 'total_qty')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cataloginventory_stock_item'),
                'total_qty',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0.0000',
                    'comment' => 'Total Qty',
                    'after' => 'qty'
                ]
            );
        }
        return $this;
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addShelfLocationFieldToStockItem(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('cataloginventory_stock_item'), 'shelf_location')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cataloginventory_stock_item'),
                'shelf_location',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Shelf Location',
                    'after' => 'total_qty'
                ]
            );
        }
        return $this;
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addWarehouseStoreViewTable(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('os_warehouse_store_view'));
        /**
         * create os_warehouse_store_view table
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('os_warehouse_store_view'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Warehouse Id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                6,
                ['unsigned' => true, 'nullable' => false],
                'Store View Id'
            )->addIndex(
                $setup->getIdxName('os_warehouse_store_view', ['warehouse_id']),
                ['warehouse_id']
            )->addIndex(
                $setup->getIdxName('os_warehouse_store_view', ['store_id']),
                ['store_id']
            )->addIndex(
                $setup->getIdxName(
                    'os_warehouse_store_view',
                    ['warehouse_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['warehouse_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    'os_warehouse_store_view',
                    'warehouse_id',
                    'os_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $setup->getTable('os_warehouse'),
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    'os_warehouse_store_view',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
        return $this;
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addUpdatedTimeFieldStockItem(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('cataloginventory_stock_item'), 'updated_time')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cataloginventory_stock_item'),
                'updated_time',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                    'comment' => 'Updated Time'
                ]
            );
        }
        return $this;
    }
}
