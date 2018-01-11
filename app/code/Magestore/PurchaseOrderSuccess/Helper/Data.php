<?php

namespace Magestore\PurchaseOrderSuccess\Helper;

/**
 * Helper Data.
 * @category Magestore
 * @package  Magestore_SupplierSuccess
 * @module   SupplierSuccess
 * @author   Magestore Developer
 */
    
/**
 * Class Data
 * @package Magestore\SupplierSuccess\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    public function getSuggestQtyProduct($productId, $warehouseId = 0) {
        $safetyQty = $this->getSafetyQty($productId);
        $availableQty = $this->getAvailableQty($productId, $warehouseId);
        $reservedQty = $this->getReservedQty($productId, $warehouseId);

        $suggestQty = $reservedQty + $safetyQty - $availableQty;

        if($suggestQty <= 0) {
            $suggestQty = 0;
        }

        return $suggestQty;
    }

    public function getSafetyQty($productId) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $defaultSafetyQty = (int)$this->scopeConfig->getValue('purchaseordersuccess/product_config/safety_quantity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $prd = $om->get('Magento\Catalog\Model\ProductFactory')->create()->load($productId);
        if($prd->getId()) {
            if($prd->getData('use_config_safety_quantity')) {
                return $defaultSafetyQty;
            }
            return (int)$prd->getData('safety_quantity');
        }
        return 0;
    }

    public function getAvailableQty($productId, $warehouseId = 0) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $resource */
        $resource = $om->create('Magento\CatalogInventory\Model\ResourceModel\Stock\Item');
        $select = $resource->getConnection()->select()
            ->from($resource->getMainTable())
            ->where('product_id = :product_id')
            ->where('website_id = :website_id');
        $stockItems = $resource->getConnection()->fetchAll($select,
            [':product_id' => $productId, ':website_id' => $warehouseId]);

        return (int)($stockItems[0]['qty']);
    }

    public function getReservedQty($productId, $warehouseId = 0) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $resource */
        $resource = $om->create('Magento\CatalogInventory\Model\ResourceModel\Stock\Item');
        $select = $resource->getConnection()->select()
            ->from($resource->getMainTable())
            ->where('product_id = :product_id')
            ->where('website_id = :website_id');
        $stockItems = $resource->getConnection()->fetchAll($select,
            [':product_id' => $productId, ':website_id' => $warehouseId]);

        return (int)($stockItems[0]['reserved_qty']);
    }
}