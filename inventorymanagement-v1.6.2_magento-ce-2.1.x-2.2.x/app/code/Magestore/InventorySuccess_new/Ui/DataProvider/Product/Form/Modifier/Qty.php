<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Field;

class Qty extends AbstractModifier
{
    const CODE_QUANTITY = 'qty';
    const CODE_QTY_CONTAINER = 'quantity_and_stock_status_qty';

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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $authorizationForceEdit = $objectManager->get('Magento\Backend\App\Action\Context')->getAuthorization()->isAllowed('Magestore_InventorySuccess::forcedit');

        if ($groupCode = $this->getGroupCodeByField($meta, self::CODE_QTY_CONTAINER)) {
            $parentChildren = &$meta[$groupCode]['children'];
            if (!empty($parentChildren[self::CODE_QTY_CONTAINER])) {
                
                $tooltip = [
                    'description' => __(
                        'This field has been disabled by InventorySuccess extension, ' .
                        'you can change qty of product in Advanced Inventory section or creating a new stock adjusment.'
                    ),
                ];                

                $parentChildren[self::CODE_QTY_CONTAINER] = array_replace_recursive(
                    $parentChildren[self::CODE_QTY_CONTAINER],
                    [
//                        'children' => [
//                            self::CODE_QUANTITY => [
//                                'arguments' => [
//                                    'data' => [
//                                        'config' => [
//                                            'tooltip' => $tooltip,
//                                            'disabled' => true,
//                                            'imports' => [
//                                                'disabled' => true,
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
                        'children' => [
                            static::CODE_QUANTITY => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'tooltip' => $tooltip,
                                            'dataScope' => static::CODE_QUANTITY,
                                            'imports' => [
                                                'disabled' =>
                                                    '!${$.parentName}.use_config_'
                                                    . static::CODE_QUANTITY
                                                    . ':checked',
                                            ],
                                            'prefer' => 'toggle',
                                            'valueMap' => [
                                                'false' => '0',
                                                'true' => '1',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'use_config_' . static::CODE_QUANTITY => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => 'number',
                                            'formElement' => Checkbox::NAME,
                                            'componentType' => Field::NAME,
                                            'description' => __('Force Edit'),
//                                            'exports' => [
//                                                'checked' =>
//                                                    '${$.parentName}'
//                                                    . static::CODE_QUANTITY
//                                                    . ':disabled',
//                                            ],
                                            'dataScope' => 'use_config_' . static::CODE_QUANTITY,
                                            'valueMap' => [
                                                'false' => '0',
                                                'true' => '1',
                                            ],
                                            'value' => '0',
                                            'disabled'=> !$authorizationForceEdit,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            } 
        }

        return $meta;
    }
}
