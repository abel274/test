<?php
namespace Magestore\InventorySuccess\Controller\DeliveryDate;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;

class SaveSession extends Action {
    protected $checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    public function execute() {
        $deliveryDate = $this->getRequest()->getParam('deliveryDate');
        $this->checkoutSession->setData('deliveryDate', $deliveryDate);
    }
}