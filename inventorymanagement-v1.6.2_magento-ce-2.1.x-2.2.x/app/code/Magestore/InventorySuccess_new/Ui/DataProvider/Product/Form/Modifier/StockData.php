<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Class StockData hides unnecessary fields in Advanced Inventory Modal
 */
class StockData extends AbstractModifier
{
    const STOCK_DATA_FIELDS = 'stock_data';
    
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

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
        $parentChildren = &$meta['advanced_inventory_modal']['children'];
        if (!empty($parentChildren[self::STOCK_DATA_FIELDS])) {
            
            $tooltip = [
                'description' => __(
                    'This field has been disabled by InventorySuccess extension, ' .
                    'you can change qty of product in Location Stocks section or creating a new stock adjusment.'
                ),
            ];               
            
            $parentChildren[self::STOCK_DATA_FIELDS] = array_replace_recursive(
                $parentChildren[self::STOCK_DATA_FIELDS],
                [
                    'children' => [
                        'qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'tooltip' => $tooltip,
                                        'disabled' => true,
                                        'imports' => [
                                            'disabled' => true,                                             
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }         

        return $meta;
    }
}
