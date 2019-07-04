<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;

class ConfigurablePanel extends \Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel
{
    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface 
     */
    protected $warehouseManagement;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;    
    
    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    private $locator;    
    
    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\Options
     */
    private $warehouseOptions;
    
    
    public function __construct(
        \Magento\Catalog\Model\Locator\LocatorInterface $locator, 
        \Magento\Framework\UrlInterface $urlBuilder,
        WarehouseManagementInterface $warehouseManagement,
        \Magestore\InventorySuccess\Model\Warehouse\Options $warehouseOptions,
        $formName, 
        $dataScopeName, 
        $dataSourceName, 
        $associatedListingPrefix = '')
    {
        parent::__construct($locator, $urlBuilder, $formName, $dataScopeName, $dataSourceName, $associatedListingPrefix);
        $this->warehouseManagement = $warehouseManagement;
        $this->urlBuilder = $urlBuilder;
        $this->warehouseOptions = $warehouseOptions;
        $this->locator = $locator;
    }
    

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $meta[static::GROUP_CONFIGURABLE]['children']['configurable-matrix']['children'] = $this->getRows();
        return $meta;
    }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getRows()
    {
        $rows = parent::getRows();
        
        $moveColumns = ['price_weight' => [], 'status' => [], 'attributes' => [], 'actionsList' => []];
        /* remove columns from row */
        foreach ($moveColumns as $key => $column) {
            $moveColumns[$key] = $rows['record']['children'][$key];
            unset($rows['record']['children'][$key]);
        }
        
        /* change label of quantity field */
        $rows['record']['children']['quantity_container']['arguments']['data']['config']['label'] = __('Available Qty');
        
        /* add Register to Warehouse field */        
        $rows['record']['children']['register_warehouse'] = [
            'arguments' => [
                'data' => [
                    'options' => $this->warehouseOptions,//->showDummyRow(),
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Select::NAME,
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'dataScope' => 'register_warehouse',
                        'label' => __('Register to Location'),
                        'required' => 1,
                        'imports' => [
                            'visible' => $this->locator->getProduct()->getId() ? '${$.provider}:${$.parentScope}.canEdit' : '${$.provider}:data.product.weight',
                            'disabled' => '!${$.provider}:${$.parentScope}.canEdit'
                        ],                        
                    ],
                ],
            ],
        ];
        
        /* add Shelf Location field */
        $rows['record']['children']['shelf_location'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'dataScope' => 'shelf_location',
                        'label' => __('Shelf Location'),
                        'imports' => [
                            'visible' => $this->locator->getProduct()->getId() ? '${$.provider}:${$.parentScope}.canEdit' : '${$.provider}:data.product.weight',
                            'disabled' => '!${$.provider}:${$.parentScope}.canEdit'
                        ],                        
                    ],
                ],
            ],
        ]; 
        
        /* add removed columns to the end of row */
        foreach ($moveColumns as $key => $column) {
            $rows['record']['children'][$key] = $column;
        }        
                
        return $rows;
    }

}
