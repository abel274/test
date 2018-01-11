<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\TransferStock;

use Magento\Framework\Model\AbstractModel as AbstractModel;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferActivityInterface;

use \Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;
use \Magestore\InventorySuccess\Model\StockActivity\ProductSelectionManagement;
use \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct as TransferStockProductResource;
use \Magestore\InventorySuccess\Model\TransferStock\StockMovementActivity\Transfer as TransferStockMovementActivity;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;

class TransferStockManagement extends ProductSelectionManagement implements
    \Magestore\InventorySuccess\Api\TransferStock\TransferStockManagementInterface
{
    /** @var  \Magestore\InventorySuccess\Model\TransferStock\TransferValidationFactory */
    protected $_transferValidationFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProductFactory */
    protected $_transferStockProductResourceFactory;

    /** @var  \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagementFactory */
    protected $_transferActivityManagementFactory;

    /**
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagementFactory $resourceProductSelectionManagementFactory,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegsitry,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\IncrementIdManagementInterface $incrementIdManagement,
        \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper,
        \Magestore\InventorySuccess\Model\TransferStock\TransferValidationFactory $transferValidationFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProductFactory $transferStockProductResourceFactory,
        \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagementFactory $transferActivityManagementFactory

    )
    {
        parent::__construct($resourceProductSelectionManagementFactory, $stockChange, $warehouseStockRegsitry, $warehouseFactory, $incrementIdManagement, $systemHelper);
        $this->_transferValidationFactory = $transferValidationFactory;
        $this->_objectManager = $objectManager;
        $this->_transferStockProductResourceFactory = $transferStockProductResourceFactory;
        $this->_transferActivityManagementFactory = $transferActivityManagementFactory;
    }


    /**
     * Generate unique code of Stock Adjustment
     *
     * @return string
     */
    public function generateCode()
    {
        return parent::generateUniqueCode(TransferStockInterface::TRANSFER_CODE_PREFIX);
    }


    /**
     * validate transfer stock general information form input
     * @param $data
     * @return array
     */
    public function validate($data)
    {
        $transferValidation = $this->_transferValidationFactory->create();
        return $transferValidation->validateTranferGeneralForm($data);
    }


    /**
     * update product stock in a warehouse by a transferStock
     * @param TransferStockInterface $transferStock
     */
    public function updateStock(TransferStockInterface $transferStock)
    {
        $products = $this->getProducts($transferStock);

        $productData = [];
        if ($products->getSize()) {

            foreach ($products as $product) {
                $productData[$product->getProductId()] = $product->getQty();
            }

            switch ($transferStock->getType()) {
                case TransferStockInterface::TYPE_SEND:
                    $warehouseId = $transferStock->getSourceWarehouseId();
                    $this->_stockChange->issue($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
                    break;
                case TransferStockInterface::TYPE_TO_EXTERNAL:
                    $warehouseId = $transferStock->getSourceWarehouseId();
                    $this->_stockChange->issue($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
                    break;
                case TransferStockInterface::TYPE_FROM_EXTERNAL:
                    $warehouseId = $transferStock->getDesWarehouseId();
                    $this->_stockChange->receive($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
                    break;
            }
        }
    }


    /**
     * update product stock in a warehouse by a transferStock
     * @param TransferStockInterface $transferStock
     */
    public function updateStockForPo(TransferStockInterface $transferStock)
    {
        $products = $this->getProducts($transferStock);

        $productData = [];
        if ($products->getSize()) {

            foreach ($products as $product) {
                $productData[$product->getProductId()] = $product->getQty();
            }

            switch ($transferStock->getType()) {
                case TransferStockInterface::TYPE_SEND:
                    $warehouseId = $transferStock->getSourceWarehouseId();
                    $this->_stockChange->issue($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
                    break;
                case TransferStockInterface::TYPE_TO_EXTERNAL:
                    $warehouseId = $transferStock->getSourceWarehouseId();
                    $this->_stockChange->issue($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
                    break;
                case TransferStockInterface::TYPE_FROM_EXTERNAL:
                    $warehouseId = $transferStock->getDesWarehouseId();
                    $this->_stockChange->receive($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
                    // check with reserved_qty for change stock product item
                    $this->changeReservedQty($warehouseId, $productData);
                    break;
            }
        }
    }

    // check qty transfer from PO with reserved_qty
    // change reserved_qty and available_qty if need
    public function changeReservedQty($warehouseId, $productData) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $om->get('Magestore\PurchaseOrderSuccess\Helper\Data');
        foreach ($productData as $productId => $qty) {
            $reservedQty = $helper->getReservedQty($productId, $warehouseId);
            if($reservedQty) {
                if($qty <= $reservedQty) {
                    $changeReservedQty = $qty;
                } else {
                    $changeReservedQty = $reservedQty;
                }
                $this->changeStockReserved($warehouseId, $productId, $changeReservedQty);
            }
        }
    }

    // change reserved_qty and available_qty
    public function changeStockReserved($warehouseId, $productId, $changeReservedQty) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $warehouseStockRegistry = $om->get('Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface');
        $queryProcess = $om->get('Magestore\InventorySuccess\Api\Db\QueryProcessorInterface');

        // Decrease reserved_qty and available_qty (qty_to_ship up => available_qty down)
        $qtyChanges = array(
            WarehouseProductInterface::QTY_TO_SHIP => $changeReservedQty,
            WarehouseProductInterface::RESERVED_QTY => -$changeReservedQty
        );

        $queryProcess->start();

        // Update for current warehouse
        $query = $warehouseStockRegistry->prepareChangeProductQty($warehouseId, $productId, $qtyChanges);
        $queryProcess->addQuery($query);

        // Update for global warehouse
        $query = $warehouseStockRegistry->prepareChangeProductQty(WarehouseProductInterface::DEFAULT_SCOPE_ID, $productId, $qtyChanges);
        $queryProcess->addQuery($query);

        $queryProcess->process();
    }


    /**
     * decrease stock of source warehouse
     * increase stock of destination warehouse
     * @param TransferStockInterface $transferStock
     */
    public function directTransferStock(TransferStockInterface $transferStock)
    {
        $products = $this->getProducts($transferStock);

        $productData = [];
        $receivingQtys = [];

        if ($products->getSize()) {
            foreach ($products as $product) {
                $productData[$product->getProductId()] = $product->getQty();
                $receivingQtys[] = ['id' => $product->getProductId(), 'qty' => $product->getQty()];
            }

            $sourceWarehouseId = $transferStock->getSourceWarehouseId();
            $desWarehouseId = $transferStock->getDesWarehouseId();

            /* update stocks in warehouse & global */
            //$this->_stockChange->massChange($sourceWarehouseId, $sourceProductData);
            // $this->_stockChange->massChange($desWarehouseId, $desProductData);
            $this->_stockChange->receive($desWarehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
            $this->_stockChange->issue($sourceWarehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
            //$this->updateReceivingQty($transferStock->getTransferstockId(),$receivingQtys);
            /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagement $transferActivityManagement */
            $transferActivityManagement = $this->_transferActivityManagementFactory->create();
            $transferActivityManagement->updateTransferstockProductQtySummary($transferStock->getTransferstockId(), $receivingQtys, TransferActivityInterface::ACTIVITY_TYPE_RECEIVING);
        }
    }

    /** set product in $data into transferstock_product table
     *  set value of qty field
     * @param $transferstockId
     * @param $data
     */
    public function saveTransferStockProduct($transferstockId, $data)
    {
        $transferStock = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock');
        $transferStock->load($transferstockId);
        $this->setProducts($transferStock, $data);
        //update qty for this current transfer stock
        $totalQty = 0;
        foreach ($data as $item) {
            $totalQty += $item["qty"];
        }
        $transferStock->setData("qty", $totalQty);
        try {
            $transferStock->save();
        } catch (\Exception $e) {

        }
    }

    public function validateStockDelivery($product_stocks, $warehouseId)
    {
        $transferValidation = $this->_transferValidationFactory->create();
        return $transferValidation->validateStock($product_stocks, $warehouseId);
    }


    public function updateReceivingQty($transferstockId, $qtys)
    {
        $transferStockProductResoure = $this->_transferStockProductResourceFactory->create();

        $field = TransferStockProductResource::FIELD_QTY_RECEIVED;
        $transferStockProductResoure->updateQty($transferstockId, $qtys, $field);
    }

    public function getSelectProductListJson($transferstockId = null)
    {
        $result = [];
        $collection = $this->getSelectProductListCollection($transferstockId);
        foreach ($collection->getItems() as $item) {
            $result[(string)$item->getBarcode()] = $item->getData();
        }
        return $this->_objectManager
            ->create('Magento\Framework\Json\EncoderInterface')
            ->encode($result);
    }

    public function getSelectProductListCollection($transferstockId = null)
    {
        $edition = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ProductMetadataInterface')
            ->getEdition();
        $rowId = strtolower($edition) == 'enterprise' ? 'row_id' : 'entity_id';
        $warehouseId = $this->getWarehouseId($transferstockId);
        $productNameAttributeId = $this->_objectManager
            ->create('Magento\Eav\Model\Config')
            ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, \Magento\Catalog\Api\Data\ProductInterface::NAME)
            ->getAttributeId();
        $collection = $this->_objectManager
            ->create('Magestore\BarcodeSuccess\Model\ResourceModel\Barcode\Collection')
            ->addFieldToSelect('barcode')
            ->addFieldToSelect('qty');
        $collection->getSelect()->joinLeft(
            ['product_entity' => $collection->getTable('catalog_product_entity')],
            'main_table.product_id = product_entity.entity_id',
            ['entity_id', 'sku']
        )->joinLeft(
            ['catalog_product_entity_varchar' => $collection->getTable('catalog_product_entity_varchar')],
            "catalog_product_entity_varchar.$rowId = product_entity.entity_id && 
            catalog_product_entity_varchar.attribute_id = $productNameAttributeId",
            ['']
        )->columns(['name' => 'catalog_product_entity_varchar.value']);
        if ($warehouseId) {
            $collection->getSelect()->joinLeft(
                ['warehouse_product' => $collection->getTable(WarehouseProductResource::MAIN_TABLE)],
                'main_table.product_id = warehouse_product.product_id  AND warehouse_product.' .
                WarehouseProductInterface::WAREHOUSE_ID . ' = ' . $warehouseId,
                '*'
            )->columns([
                'available_qty' => new \Zend_Db_Expr('warehouse_product.qty'),
            ])->where(
                'warehouse_product.' . WarehouseProductInterface::WAREHOUSE_ID . ' = ?',
                $warehouseId
            )->group('main_table.barcode');
        } else {
            $collection->getSelect()->joinLeft(
                ['stock_item' => $collection->getTable('cataloginventory_stock_item')],
                'main_table.product_id = stock_item.product_id AND stock_item.' .
                WarehouseProductInterface::WAREHOUSE_ID . ' = ' . WarehouseProductInterface::DEFAULT_SCOPE_ID,
                ['']
            )->columns([
                'available_qty' => 'stock_item.qty',
            ]);
        }
        return $collection;
    }

    public function getWarehouseId($transferstockId = null)
    {
        $warehouseId = 0;
        if ($transferstockId) {
            $transferStock = $this->_objectManager
                ->create('Magestore\InventorySuccess\Model\TransferStock')
                ->load($transferstockId);
            $warehouseId = $transferStock->getSourceWarehouseId();
            if ($transferStock->getType() == \Magestore\InventorySuccess\Model\TransferStock::TYPE_FROM_EXTERNAL) {
                $warehouseId = 0;
            }
        }
        return $warehouseId;
    }

}
