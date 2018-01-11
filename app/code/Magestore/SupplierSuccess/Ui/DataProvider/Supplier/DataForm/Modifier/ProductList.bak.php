<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\SupplierSuccess\Ui\DataProvider\Supplier\DataForm\Modifier;

use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Field;

/**
 * Data provider for Configurable panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductList extends AbstractModifier
{

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getSession(\Magestore\SupplierSuccess\Api\Data\SupplierInterface::SUPPLIER_ID)) {
            $meta = array_replace_recursive(
                $meta,
                $this->getProductListing($meta)
            );
        }
        return $meta;
    }

    /**
     * @param $meta
     * @return mixed
     */
    public function getProductListing($meta)
    {
        $meta['product_listing']['arguments']['data']['config'] = [
            'label' => __('Products'),
            'collapsible' => true,
            'visible' => true,
            'opened' => true,
            'componentType' => Form\Fieldset::NAME
        ];
        $meta['product_listing']['children'] = [
            'product_listing_button' => $this->getProductListingButtons(),
            'product_list' => $this->getProductListingChildren()
        ];
//        \Zend_Debug::dump($meta);die();
        return $meta;
    }

    /**
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getProductListingButtons()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => '',
                        'template' => 'ui/form/components/complex',
                        'dataScope' => 'data',
                    ],
                ],
            ],
            'children' => array_replace_recursive(
                $this->getDeleteButtons(),
                $this->getImportButtons()
            )
        ];
    }


    /**
     * Returns Delete Button
     *
     * @return array
     */
    protected function getDeleteButtons()
    {
        return $this->getModalButton(
            'product_delete',
            'container',
            'container',
            'Magento_Ui/js/form/components/button',
            [
                [
                    'targetName' =>
                        'os_dropship_form.os_dropship_form.add_test1_modal',
                    'actionName' => 'openModal',
                ],
                [
                    'targetName' =>
                        'os_dropship_form.os_dropship_form.add_test1_modal.test1_product_listing',
                    'actionName' => 'destroyInserted',
                ],
                [
                    'targetName' =>
                        'os_dropship_form.os_dropship_form.add_test1_modal.test1_product_listing',
                    'actionName' => 'render',
                ]
            ],
            __('Delete Product')
        );
    }

    /**
     * Returns Delete Button
     *
     * @return array
     */
    protected function getImportButtons()
    {
        return $this->getModalButton(
            'product_import',
            'container',
            'container',
            'Magento_Ui/js/form/components/button',
            [
                [
                    'targetName' =>
                        'os_dropship_form.os_dropship_form.add_test1_modal',
                    'actionName' => 'openModal',
                ],
                [
                    'targetName' =>
                        'os_dropship_form.os_dropship_form.add_test1_modal.test1_product_listing',
                    'actionName' => 'destroyInserted',
                ],
                [
                    'targetName' =>
                        'os_dropship_form.os_dropship_form.add_test1_modal.test1_product_listing',
                    'actionName' => 'render',
                ]
            ],
            __('Import')
        );
    }

    /**
     * @return array
     */
    public function getProductListingChildren()
    {
        $productListing = 'os_supplier_product_listing';
        $grid = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'sortOrder' => 10,

                    ],
                ],
            ],
            'children' => [
                'html_content' => [
                    'arguments' => [
                        'data' => [
                            'type' => 'html_content',
                            'name' => 'html_content',
                            'config' => [
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/html',
                                'content' => \Magento\Framework\App\ObjectManager::getInstance()
                                    ->create('Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Stock')
                                    ->toHtml()
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $grid;
    }
}
