<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\SupplierSuccess\Model\ResourceModel\Supplier;

/**
 * Class Product
 * @package Magestore\SupplierSuccess\Model\ResourceModel\Supplier
 */
class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('os_supplier_product', 'supplier_product_id');
    }

    /**
     * @param $data
     * $data = [
     *  'supplier_id' => int,
     *  'product_id' => int,
     *  'product_sku' => string,
     *  'product_name' => string,
     *  'product_supplier_sku' => string,
     *  'cost' => float,
     *  'tax' => float
     * ]
     */
    public function addProducts($data)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('os_supplier_product');
        return $connection->insertOnDuplicate($table, $data);
    }
}
