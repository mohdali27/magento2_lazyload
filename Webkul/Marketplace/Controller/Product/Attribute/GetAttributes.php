<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Product\Attribute;

use \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Marketplace Product GetAttributes controller.
 */
class GetAttributes extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @param \Magento\Framework\App\Action\Context             $context
     * @param CollectionFactory $attributeCollection
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CollectionFactory $attributeCollection
    ) {
        $this->_attributeCollection = $attributeCollection;
        parent::__construct($context);
    }

    /**
     * Get Eav Attributes action.
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $ids = $this->getRequest()->getParam('attributes');
            $collection = $this->_attributeCollection->create();
            $collection->addFieldToFilter('main_table.attribute_id', $ids);
            $attributes = [];
            foreach ($collection->getItems() as $attribute) {
                $attributes[] = [
                    'id' => $attribute->getId(),
                    'label' => $attribute->getFrontendLabel(),
                    'code' => $attribute->getAttributeCode(),
                    'options' => $attribute->getSource()->getAllOptions(false),
                    'canCreateOption' => !$this->isSwatchTypeAttribute($attribute)
                ];
            }
            $attributesArray = $attributes;
            $this->getResponse()->representJson(
                $this->_objectManager->get(
                    'Magento\Framework\Json\Helper\Data'
                )->jsonEncode($attributesArray)
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Check if an attribute is Swatch
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function isSwatchTypeAttribute(Attribute $attribute)
    {
        $result = $this->isVisualTypeSwatch($attribute) || $this->isTextTypeSwatch($attribute);
        return $result;
    }

    /**
     * If attribute is visual swatch
     *
     * @param Attribute
     * @return bool
     */
    public function isVisualTypeSwatch(Attribute $attribute)
    {
        if (!$attribute->hasData('swatch_input_type')) {
            $this->populateAdditionalDataAttribute($attribute);
        }
        return $attribute->getData('swatch_input_type') == 'visual';
    }

    /**
     * If attribute is textual swatch
     *
     * @param Attribute
     * @return bool
     */
    public function isTextTypeSwatch(Attribute $attribute)
    {
        if (!$attribute->hasData('swatch_input_type')) {
            $this->populateAdditionalDataAttribute($attribute);
        }
        return $attribute->getData('swatch_input_type') == 'text';
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    private function populateAdditionalDataAttribute(Attribute $attribute)
    {
        $attrAdditionalData = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Orders'
        )->getProductOptions(
            $attribute->getData('additional_data')
        );
        if (!empty($attrAdditionalData) && is_array($attrAdditionalData)) {
            $attributeAdditionalDataKeys = [
                'swatch_input_type',
                'update_product_preview_image',
                'use_product_image_for_swatch'
            ];
            foreach ($attributeAdditionalDataKeys as $key) {
                if (!empty($attrAdditionalData[$key])) {
                    $attribute->setData($key, $attrAdditionalData[$key]);
                }
            }
        }
        return $attribute;
    }
}
