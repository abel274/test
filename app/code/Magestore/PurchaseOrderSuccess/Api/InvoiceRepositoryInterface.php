<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Api;

/**
 * @api
 */
interface InvoiceRepositoryInterface
{

    /**
     * Get list purchase order invoice that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magestore\PurchaseOrderSuccess\Api\Data\InvoiceSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
    
    /**
     * Get a purchase order invoice by id.
     *
     * @param int $id purchase order invoice id
     * @return \Magestore\PurchaseOrderSuccess\Api\Data\InvoiceInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function get($id);
    
    /**
     * Create purchase order invoice
     *
     * @param \Magestore\PurchaseOrderSuccess\Api\Data\InvoiceInterface $invoice
     * @return \Magestore\PurchaseOrderSuccess\Api\Data\InvoiceInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magestore\PurchaseOrderSuccess\Api\Data\InvoiceInterface $invoice);
    
    /**
     * Deletes a specified purchase order invoice.
     *
     * @param \Magestore\PurchaseOrderSuccess\Api\Data\InvoiceInterface $invoice
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @return bool
     */
    public function delete(\Magestore\PurchaseOrderSuccess\Api\Data\InvoiceInterface $invoice);    
    
    /**
     * Deletes a specified purchase order invoice by id.
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);
}