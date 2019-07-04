<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Locations\Modifier;


use Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\DynamicRows;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magestore\InventorySuccess\Model\Options\Location\Warehouse;
use Magestore\InventorySuccess\Model\Options\Warehouse\Location;

/**
 * Class ModifierFormDataProvider
 * @package Magestore\InventorySuccess\Ui\DataProvider\Permission\Staff\Modifier
 */
class ModifierFormDataProvider extends Dynamic
{
    /**
     * @var Warehouse
     */
    protected $_warehouseOptions;

    /**
     * @var Location
     */
    protected $_locationOptions;
    /**
     * Group Container
     *
     * @var string
     */
    protected $_groupContainer = 'warehouse_location';

    /**
     * Group Template
     *
     * @var string
     */
    protected $_groupLabel = '';

    /**
     * Fieldset Content
     *
     * @var string
     */
    protected $_fieldsetContent = '';

    /**
     * Data Links Type
     *
     * @var string
     */
    protected $_dataLinks = 'mapping';

    /**
     * Group Container
     *
     * @var string
     */
    protected $_opened = true;

    /**
     * Button Title
     *
     * @var string
     */
    protected $_buttonTitle = 'Choose Locations';

    /**
     * Modal Title
     *
     * @var string
     */
    protected $_modalTitle = '';


    /**
     * Modal Button Title
     *
     * @var string
     */
    protected $_modalButtonTitle = 'Add Selected Locations';

    /**
     * Modifier Config
     *
     * @var array
     */
    protected $_modifierConfig = [
        'button_set' => 'warehouse_location_button_set',
        'modal' => 'warehouse_location_modal',
        'listing' => 'os_webpos_locations_listing',
        'columns_ids' => 'os_webpos_locations_listing_columns.ids',
        'form' => 'os_warehouse_locations_mapping_form'
    ];

    /**
     * Fields Map
     *
     * @var array
     */
    protected $_mapFields = [
        'id' => 'location_id',
        'location_id' => 'location_id',
        'display_name' => 'display_name',
        'warehouse_id' => 'warehouse_id',
        'position' => 'position'
    ];

    public function __construct(
        UrlInterface $urlBuilder,
        RequestInterface $request,
        Warehouse $warehouseOptions,
        Location $locationOptions,
        array $modifierConfig = []
    )
    {
        parent::__construct($urlBuilder, $request, $modifierConfig);
        $this->_warehouseOptions = $warehouseOptions;
        $this->_locationOptions = $locationOptions;
    }

    /**
     * Fill meta columns
     *
     * @return array
     */
    protected function fillModifierMeta()
    {
        return [
            'id' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => 'id',
                            'sortOrder' => 1,
                            'visible' => false,
                        ],
                    ],
                ],
            ],
            'location_id' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => 'location_id',
                            'sortOrder' => 10,
                            'label' => __('Location'),
                            'visible' => false,
                        ],
                    ],
                ],
            ],
            'display_name' => $this->getTextColumn('display_name', false, __('Location'), 15),
            'warehouse_id' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'formElement' => Form\Element\Select::NAME,
                            'componentType' => Form\Field::NAME,
                            'component' => 'Magestore_InventorySuccess/js/form/element/select',
                            'elementTmpl' => 'Magestore_InventorySuccess/form/element/select',
                            'dataScope' => 'warehouse_id',
                            'sortOrder' => 20,
                            'label' => __('Location'),
                            'options' => $this->_warehouseOptions->getAllOptionArray(),
                        ],
                    ],
                ],
            ],
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 90,
                            'fit' => true,
                        ],
                    ],
                ],
            ],
            'position' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => 'position',
                            'sortOrder' => 100,
                            'visible' => false,
                        ],
                    ],
                ],
            ]
        ];
    }
}
