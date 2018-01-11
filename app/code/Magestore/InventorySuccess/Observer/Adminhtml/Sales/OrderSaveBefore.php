<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Adminhtml\Sales;

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

    protected $session;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
        $this->session = $session;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer) {
        // set data delivery_date for order
        $order = $observer->getEvent()->getOrder();
        $deliveryDate = $this->session->getData('deliveryDate');
        // only set delivery date for new order
        if(!$order->getId()) {
            if(!$deliveryDate) {
                $deliveryDate = new \DateTime('now', new \DateTimeZone('UTC'));
                $deliveryDate = $deliveryDate->format('Y-m-d');
            }
            $deliveryDate = strtotime($deliveryDate);
            $order->setData('delivery_date', $deliveryDate);
        }
    }

}
