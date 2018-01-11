<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\SupplierSuccess\Setup;

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
            $this->addScheduleFieldForSupplier($setup);
        }
    }

    protected function addScheduleFieldForSupplier(SchemaSetupInterface $setup) {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_supplier'), 'gaptime')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_supplier'),
                'gaptime',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Gap time'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_supplier'), 'numberDayPerTime')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_supplier'),
                'numberDayPerTime',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Number Day Per Time'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_supplier'), 'schedule')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_supplier'),
                'schedule',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Schedule'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_supplier'), 'monthOfYear')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_supplier'),
                'monthOfYear',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Month Of Year'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_supplier'), 'dayOfMonth')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_supplier'),
                'dayOfMonth',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Day Of Month'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_supplier'), 'monthOfQuarter')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_supplier'),
                'monthOfQuarter',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Month Of Quarter'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_supplier'), 'dayOfWeek')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_supplier'),
                'dayOfWeek',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Day Of Week'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_supplier'), 'startDate')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_supplier'),
                'startDate',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Start Date'
                ]
            );
        }
    }
}
