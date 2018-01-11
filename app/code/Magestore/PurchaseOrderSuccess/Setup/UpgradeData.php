<?php
namespace Magestore\PurchaseOrderSuccess\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface {


    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->_eavAttribute = $eavAttribute;
        $this->productMetadata = $productMetadata;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

//            $eavSetup->removeAttribute(
//                \Magento\Catalog\Model\Product::ENTITY,
//                'safety_quantity');
            $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'use_config_safety_quantity');

            /**
             * Add attributes to the eav/attribute
             */

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'safety_quantity',
                [
                    'group' => 'General',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Safety Quantity',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 10,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'sort_order' => 300,
                    'position' => 300,
                    'apply_to' => ''
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'use_config_safety_quantity',
                [
                    'group' => 'General',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Use Default Safety Quantity',
                    'input' => 'select',
                    'class' => '',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::VALUE_YES,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'sort_order' => 310,
                    'position' => 310,
                    'apply_to' => ''
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            // set default use_config_safety_quantity

            $attributeId = $this->_eavAttribute->getIdByCode('catalog_product', 'use_config_safety_quantity');
            $action = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magento\Catalog\Model\ResourceModel\Product\Action'
            );
            $connection = $action->getConnection();
            $table = $setup->getTable('catalog_product_entity_int');
            $productCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
                'Magento\Catalog\Model\ResourceModel\Product\Collection'
            );
            $version = $this->productMetadata->getVersion();
            $edition = $this->productMetadata->getEdition();
            foreach($productCollection->getAllIds() as $productId){
                if($edition == 'Enterprise' && version_compare($version, '2.1.5', '>=')){
                    $data = [
                        'attribute_id'  => $attributeId,
                        'store_id'  => 0,
                        'row_id' => $productId,
                        'value' => 1
                    ];
                }else{
                    $data = [
                        'attribute_id'  => $attributeId,
                        'store_id'  => 0,
                        'entity_id' => $productId,
                        'value' => 1
                    ];
                }
                $connection->insertOnDuplicate($table, $data, ['value']);
            }
        }
    }
}