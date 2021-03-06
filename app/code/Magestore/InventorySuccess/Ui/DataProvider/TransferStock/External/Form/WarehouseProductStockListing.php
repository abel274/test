<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\External\Form;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magestore\InventorySuccess\Model\TransferStock\TransferStockProductFactory;


class WarehouseProductStockListing extends AbstractDataProvider
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var use Magestore\InventorySuccess\Model\TransferStock\TransferStockProductFactory
     */
    protected $_transferStockProductFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $_warehouseSource;

    private $_registry;

    /**
     * Generate constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        \Magestore\InventorySuccess\Model\TransferStock\TransferStockProductFactory $transferStockProductFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        \Magento\Framework\Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create()->PrepareProductListCollection(3);
        $this->urlBuilder = $urlBuilder;
        $this->_transferStockProductFactory = $transferStockProductFactory;
        $this->_warehouseSource = $warehouseSource;

    }


}