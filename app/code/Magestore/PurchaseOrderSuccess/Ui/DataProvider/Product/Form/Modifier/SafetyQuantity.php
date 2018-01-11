<?php

namespace Magestore\PurchaseOrderSuccess\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Field;

class SafetyQuantity extends AbstractModifier
{
    public function modifyMeta(array $meta)
    {
        $safety_quantity = $meta['product-details']['children']['container_safety_quantity']['children']['safety_quantity'];
        $safety_quantity['arguments']['data']['config']['imports'] =
            [
                'disabled' =>
                    '${$.parentName}.use_config_safety_quantity'
                    . ':checked',
            ];
        $safety_quantity['arguments']['data']['config']['prefer'] = 'toggle';
        $safety_quantity['arguments']['data']['config']['validation'] = [
            'validate-zero-or-greater' => true
        ];
        $safety_quantity['arguments']['data']['config']['valueMap'] = [
            'false' => '0',
            'true' => '1',
        ];

        $children = $meta['product-details']['children']['container_safety_quantity']['children'];
        $children['safety_quantity'] = $safety_quantity;
        $children['use_config_safety_quantity'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType' => 'number',
                        'formElement' => Checkbox::NAME,
                        'componentType' => Field::NAME,
                        'description' => __('Use Default'),
                        'label' => __(' '),
                        'dataScope' => 'use_config_safety_quantity',
                        'valueMap' => [
                            'false' => '0',
                            'true' => '1',
                        ],
                        'disabled'=> false,
                    ],
                ],
            ],
        ];

        $meta['product-details']['children']['container_safety_quantity']['children'] = $children;
        unset($meta['product-details']['children']['container_use_config_safety_quantity']);

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}