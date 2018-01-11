<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\SupplierSuccess\Model\ResourceModel\Supplier\Grid;

use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class ProductDataProvider
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @inheritdoc
     */
    protected $document = Document::class;

    const MAPPING_FIELDS = [
        'total_sku'=> 'COUNT(DISTINCT(supplier_product.supplier_product_id))',
        'last_purchase_order_on'=> 'MAX(purchase_order.purchased_at)',
        'purchase_order_value'=> 'SUM(DISTINCT(IFNULL(purchase_order.grand_total_incl_tax,0)))',
    ];

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'os_supplier',
        $resourceModel = 'Magestore\SupplierSuccess\Model\ResourceModel\Supplier'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()])
            ->joinLeft(
                ['supplier_product' => $this->getTable('os_supplier_product')],
                'main_table.supplier_id = supplier_product.supplier_id',
                []
            )
            ->joinLeft(
                ['purchase_order' => $this->getTable('os_purchase_order')],
                'main_table.supplier_id = purchase_order.supplier_id',
                []
            )
            ->columns(
                [
                    'total_sku' => new \Zend_Db_Expr(self::MAPPING_FIELDS['total_sku']),
                    'last_purchase_order_on' => new \Zend_Db_Expr(self::MAPPING_FIELDS['last_purchase_order_on']),
                    'purchase_order_value' => new \Zend_Db_Expr(self::MAPPING_FIELDS['purchase_order_value']),
                ]
            )
            ->group('main_table.supplier_id');
        return $this;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        foreach (self::MAPPING_FIELDS as $alias => $column) {
            if($field == $alias){
                $field = new \Zend_Db_Expr($column);
            }
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        foreach (self::MAPPING_FIELDS as $alias => $column) {
            if($field == $alias){
                $field = new \Zend_Db_Expr($column);
            }
        }
        return parent::setOrder($field, $direction);
    }
}
