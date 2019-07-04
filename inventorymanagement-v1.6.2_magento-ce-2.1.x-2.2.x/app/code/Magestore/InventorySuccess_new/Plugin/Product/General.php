<?php

namespace Magestore\InventorySuccess\Plugin\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier as AbstractModifier;

class General
{

    /**
     * @var LocatorInterface
     */
    protected $locator;

    protected $qty = [];

    public function __construct(
        LocatorInterface $locator
    ) {
        $this->locator = $locator;
    }

    public function beforeModifyData(\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General $subject, $data)
    {
        $modelId = $this->locator->getProduct()->getId();
        $fieldCode = ProductAttributeInterface::CODE_TIER_PRICE;
        if (isset($data[$modelId][AbstractModifier::DATA_SOURCE_DEFAULT][$fieldCode])) {
            foreach ($data[$modelId][AbstractModifier::DATA_SOURCE_DEFAULT][$fieldCode] as &$value) {
                $this->qty[$value['price_id']] = $value[ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE_QTY];
            }
        }
        return ;
    }
    public function afterModifyData(\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General $subject, $data)
    {

        $modelId = $this->locator->getProduct()->getId();
        $fieldCode = ProductAttributeInterface::CODE_TIER_PRICE;
        if(isset($data[$modelId]['product']['stock_data']['is_qty_decimal'])){
            if(!$data[$modelId]['product']['stock_data']['is_qty_decimal']){
                return $data;
            }
        }
        if (isset($data[$modelId][AbstractModifier::DATA_SOURCE_DEFAULT][$fieldCode])) {
            foreach ($data[$modelId][AbstractModifier::DATA_SOURCE_DEFAULT][$fieldCode] as &$value) {
                $value[ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE_QTY] = $this->qty[$value['price_id']];
            }
        }
        return $data;
    }

}