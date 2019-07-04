<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Rule\Condition;

class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\Feed\Model\Rule\Condition\ProductFactory $conditionFactory,
        array $data = []
    ) {
        parent::__construct($context, $conditionFactory, $data);

        $this->setType('Amasty\Feed\Model\Rule\Condition\Combine');
    }

    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();

        $productAttributes['type_id'] = __('Type');

        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Amasty\Feed\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Amasty\Feed\Model\Rule\Condition\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        return $conditions;
    }
}
