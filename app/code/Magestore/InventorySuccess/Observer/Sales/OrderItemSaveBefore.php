<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class OrderItemSaveBefore implements ObserverInterface {

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface  
     */
    protected $_objectManager;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager, 
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer) {
        $orderItem = $observer->getEvent()->getItem();
        $beforeItem = $this->_objectManager->create('Magento\Sales\Model\Order\Item');
        if($orderItem->getId()) {
            $beforeItem->load($orderItem->getId());
        }
        if (!$this->_coreRegistry->registry('os_beforeOrderItem' . $orderItem->getId())) {
            $this->_coreRegistry->register('os_beforeOrderItem' . $orderItem->getId(), $beforeItem);
        }

        if(!$orderItem->getId()) {
            // set data is_after_restock_date for sales_order_item
            $order = $orderItem->getOrder();
            // get delivery date by session
//        $deliveryDate = $this->_objectManager->get('Magento\Checkout\Model\Session')->getData('deliveryDate');
//        if(!$deliveryDate) {
//            $deliveryDate = $this->_objectManager->get('Magento\Backend\Model\Session')->getData('deliveryDate');
//        }
            // get delivery date by order
            $deliveryDate = date('Y-m-d', $order->getDeliveryDate());
            $is_after_restock_date = 0;
            // only add for new item order
            if ($deliveryDate) {
                $deliveryDate = strtotime($deliveryDate);
                $prdId = $orderItem->getData('product_id');
                $restockDate = $this->_objectManager
                    ->get('Magestore\SupplierSuccess\Helper\Data')
                    ->getRestockDate($prdId);

                if ($restockDate) {
                    if ($deliveryDate > strtotime($restockDate)) {
                        $is_after_restock_date = 1;
                        $orderItem->setData('is_after_restock_date', $is_after_restock_date);
                    }
                }
            }
        }
    }

}
