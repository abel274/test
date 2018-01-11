<?php
namespace Magestore\InventorySuccess\Controller\Adminhtml\DeliveryDate;

use Magento\Backend\App\Action;

class SaveSession extends Action {
    protected $session;

    public function __construct(
        \Magento\Backend\Model\Session $session,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->session = $session;
    }

    public function execute() {
        $deliveryDate = $this->getRequest()->getParam('deliveryDate');
        if(!$deliveryDate) {
            $deliveryDate = new \DateTime('now', new \DateTimeZone('UTC'));
            $deliveryDate = $deliveryDate->format('Y-m-d');
        }
        $this->session->setData('deliveryDate', $deliveryDate);
    }
}