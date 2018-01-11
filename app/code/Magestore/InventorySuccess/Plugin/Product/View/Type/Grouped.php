<?php
namespace Magestore\InventorySuccess\Plugin\Product\View\Type;

class Grouped extends \Magento\GroupedProduct\Block\Product\View\Type\Grouped {
    public function getTemplate() {
        if($this->getNameInLayout() != 'product.info.grouped') {
            return 'Magestore_InventorySuccess::product/view/type/grouped/default.phtml';
        } else {
            return parent::getTemplate();
        }
    }
}