<?php

namespace Magestore\PurchaseOrderSuccess\Observer\Catalog;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductSaveBefore implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $observer->getEvent()->getProduct();
        $request = $om->get('Magento\Framework\App\RequestInterface');
        $data = $request->getParams();
        $scopeConfig = $om->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $default_safety_qty = $scopeConfig->getValue('purchaseordersuccess/product_config/safety_quantity');
        if($data['product']['use_config_safety_quantity'] == 1
            || $data['product']['safety_quantity'] == null) {
            $product->setData('safety_quantity', $default_safety_qty);
        }
    }
}