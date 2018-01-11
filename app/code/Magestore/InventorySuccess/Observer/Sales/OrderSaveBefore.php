<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class OrderSaveBefore implements ObserverInterface {

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

    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer) {
        // set data delivery_date for order
        $order = $observer->getEvent()->getOrder();
        $deliveryDate = $this->checkoutSession->getData('deliveryDate');
        // only set delivery date for new order
        if($deliveryDate && !$order->getId()) {
            $deliveryDate = strtotime($deliveryDate);
            $order->setData('delivery_date', $deliveryDate);
        }
    }

}
