<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\External\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Model\TransferStock as TransferStockModel;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExternalStock extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory
     */
    protected $collection;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStockFactory
     */
    protected $_transferStockFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\TransferStock
     */
    protected $_transferStockResource;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $warehouseSource;

    /** @var  \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement */
    protected $_transferStockManagement;

    /** @var  \Magestore\InventorySuccess\Model\Locator\LocatorFactory */
    protected $_locatorFactory;

    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory */
    protected $_collectionFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * PermissionManagementInterface
     *
     * @var \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface
     */
    protected $_permissionManagement;


    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock $transferStockResource,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $collectionFactory,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagementInterface,

        array $_modifierConfig = []
    ) {
        parent::__construct($urlBuilder, $request, $_modifierConfig);
        $this->collection = $collectionFactory->create();
        $this->_transferStockFactory = $transferStockFactory;
        $this->_transferStockResource = $transferStockResource;
        $this->_warehouseSource = $warehouseSource;
        $this->urlBuilder = $urlBuilder;
        $this->_transferStockManagement = $transferStockManagement;
        $this->_coreRegistry = $registry;
        $this->_locatorFactory = $locatorFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_permissionManagement = $permissionManagementInterface;
    }

    /**
     * Get current Adjustment
     *
     * @return Adjustment
     * @throws NoSuchEntityException
     */
    public function getCurrentTransferStock()
    {
        $transferStock = [];
        $requestId = $this->request->getParam('id');
        if ($requestId) {
            $transferStock = $this->_transferStockFactory->create();
            $this->_transferStockResource->load($transferStock, $requestId);
        }
        return $transferStock;
    }

    /**
     * Get adjust stock status
     *
     * @return string
     */
    public function getTransferStockStatus()
    {
        $transferStock = $this->getCurrentTransferStock();
        if($transferStock){
            return $transferStock->getStatus();
        }
        return null;
    }

    /**
     * is disabled element
     *
     * @param
     * @return
     */
    public function isDisabledElement()
    {
        if ($this->request->getParam('id'))
            return 'disabled';
        return false;
    }

    /**
     * get collapsible
     *
     * @param
     * @return boolean
     */
    public function getCollapsible(){
        if ($this->getTransferStockStatus() != TransferStockModel::STATUS_COMPLETED)
            return $this->_collapsible;
        return false;
    }

    /**
     * get group label
     *
     * @param
     * @return boolean
     */
    public function getGroupLabel(){
        if ($this->getTransferStockStatus() != TransferStockModel::STATUS_COMPLETED)
            return $this->_groupLabel;
        return '';
    }

    /**
     * get modify tmpl
     *
     * @param
     * @return
     */
    public function getModifyTmpl($type)
    {
        if ($this->getTransferStockStatus() == TransferStockModel::STATUS_COMPLETED) {
            return static::TMPL_TEXT_LABEL;
        }
        switch ($type){
            case 'input':
                return static::TMPL_TEXT_LABEL;
                break;
            case 'textarea':
                return static::TMPL_TEXTAREA_LABEL;
                break;
            case 'select':
                return static::TMPL_SELECT_LABEL;
                break;
            default:
                return static::TMPL_TEXT_LABEL;
        }
    }

    public function getElementTmpl($type, $canEditLater){
        $result = static::TMPL_TEXT_LABEL;
        if (!$this->getTransferStockStatus()) {
            switch ($type){
                case 'input':
                    $result = static::TMPL_INPUT;
                    break;
                case 'textarea':
                    $result = static::TMPL_TEXTAREA;
                    break;
                case 'select':
                    $result = static::TMPL_SELECT;
                    break;
                default:
                    $result = static::TMPL_INPUT;
            }
        }
        else{
            if($this->getTransferStockStatus() != TransferStockInterface::STATUS_PENDING){
                $canEditLater = false;
            }

            if($canEditLater){
                switch ($type){
                    case 'input':
                        $result = static::TMPL_INPUT;
                        break;
                    case 'textarea':
                        $result = static::TMPL_TEXTAREA;
                        break;
                    case 'select':
                        $result = static::TMPL_SELECT;
                        break;
                    default:
                        $result = static::TMPL_INPUT;
                }
            }
            else{
                switch ($type){
                    case 'input':
                        $result = static::TMPL_TEXT_LABEL;
                        break;
                    case 'textarea':
                        $result = static::TMPL_TEXTAREA_LABEL;
                        break;
                    case 'select':
                        $result = static::TMPL_SELECT_LABEL;
                        break;
                    default:
                        $result = static::TMPL_TEXT_LABEL;
                }
            }
        }
        return $result;
    }
}
