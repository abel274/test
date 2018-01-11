<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\SupplierSuccess\Controller\Adminhtml\Supplier\Product;

class Delete extends \Magestore\SupplierSuccess\Controller\Adminhtml\AbstractSupplier
{
    const ADMIN_RESOURCE = 'Magestore_SupplierSuccess::view_supplier';
    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $data = $this->getRequest()->getPostValue();
        if (isset($data['selected']) || isset($data['excluded'])) {
            /** @var \Magestore\SupplierSuccess\Model\ResourceModel\Supplier\Product\Collection $supplierProductCollection */
            $supplierId = $this->getRequest()->getParam('supplier_id');
            $supplierProductCollection = $this->supplierProductCollectionFactory->create();
            $supplierProductCollection->addFieldToFilter('supplier_id', $supplierId);
            $filter = \Magento\Framework\App\ObjectManager::getInstance()->create(
                'Magento\Ui\Component\MassAction\Filter'
            );
            $supplierProductCollection = $filter->getCollection($supplierProductCollection);
//            if (isset($data['selected'])) {
//                $supplierProductCollection->addFieldToFilter('product_id', ['in' => $data['selected']]);
//            }
//            if (isset($data['excluded'])) {
//                $supplierProductCollection->addFieldToFilter('product_id', ['nin' => $data['excluded']]);
//            }
            if ($supplierProductCollection->getSize()) {
                try {
                    foreach ($supplierProductCollection as $supplierProduct) {
                        $this->supplierProductRepositoryInterface->delete($supplierProduct);
                    }
                    $this->messageManager->addSuccessMessage(__('Products in this supplier have been deleted.'));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
            }
        }
    }
}
