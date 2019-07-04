<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\Stocktaking\Form\Modifier;

use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Model\Stocktaking as StocktakingModel;
use Magestore\InventorySuccess\Ui\DataProvider\Product\Form\Modifier\Stock;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductList extends \Magestore\InventorySuccess\Ui\DataProvider\Stocktaking\Form\Modifier\Stocktaking
{

    /**
     * @var string
     */
    protected $_groupContainer = 'os_stocktaking';

    /**
     * @var string
     */
    protected $_dataLinks = 'product_list';

    /**
     * @var string
     */
    protected $_fieldsetContent = 'Please add or import products to stocktake';

    /**
     * @var string
     */
    protected $_buttonTitle = 'Add Products to Stocktake';

    /**
     * @var string
     */
    protected $_modalTitle = 'Add Products to Stocktake';

    /**
     * @var string
     */
    protected $_modalDataId = 'stocktaking_id';

    /**
     * @var string
     */
    protected $_modalDataColumn = 'warehouse_id';

    /**
     * @var array
     */
    protected $_modifierConfig = [
        'button_set' => 'product_stock_button_set',
        'modal' => 'product_stock_modal',
        'listing' => 'os_stocktaking_product_listing',
        'form' => 'os_stocktaking_form',
        'columns_ids' => 'product_columns.ids',
    ];

    /**
     * @var array
     */
    protected $_mapFields = [
        'id' => 'entity_id',
        'sku' => 'sku',
        'name' => 'name',
        'total_qty' => 'total_qty',
        'stocktaking_qty' => 'stocktaking_qty',
        'stocktaking_reason' => 'stocktaking_reason',
        'image' => 'image_url',
    ];

    /**
     * get button title
     *
     * @param
     * @return
     */
    public function getButtonTitle(){
        if($this->getStocktakingStatus() == StocktakingModel::STATUS_PROCESSING)
            return 'Add products to count';
        return $this->_buttonTitle;
    }

    /**
     * get modal title
     *
     * @param
     * @return
     */
    public function getModalTitle(){
        if($this->getStocktakingStatus() == StocktakingModel::STATUS_PROCESSING)
            return 'Add products to count';
        return $this->_modalTitle;
    }

    /**
     * get modal title
     *
     * @param
     * @return
     */
    public function getImportTitle(){
        if($this->getStocktakingStatus() == StocktakingModel::STATUS_PROCESSING)
            return 'Import products to count';
        return $this->_importTitle;
    }

    /**
     * add import product button to stocktake
     *
     * @param
     * @return
     */
    protected function getAdditionalButtons()
    {
        return [
            'import_button' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'component' => 'Magestore_InventorySuccess/js/element/import-button',
                            'actions' => [],
                            'title' => $this->getImportTitle(),
                            'provider' => null,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * get fieldset content
     *
     * @param
     * @return
     */
    public function getFieldsetContent(){
        if ($this->getStocktakingStatus() != StocktakingModel::STATUS_COMPLETED
        && $this->getStocktakingStatus() != StocktakingModel::STATUS_VERIFIED)
            return $this->_fieldsetContent;
        return '';
    }

    /**
     * get use button set
     *
     * @param
     * @return
     */
    public function getUseButtonSet(){
        if ($this->getStocktakingStatus() != StocktakingModel::STATUS_COMPLETED
            && $this->getStocktakingStatus() != StocktakingModel::STATUS_VERIFIED)
            return $this->_useButtonSet;
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $modelId = $this->request->getParam('id');
        if ($modelId) {
            $products = $this->collection->getStocktakingProducts($modelId);
            $data[$modelId]['links'][$this->_dataLinks] = [];
            if($products->getSize() > 0) {
                foreach ($products as $product) {
                    $data[$modelId]['links'][$this->_dataLinks][] = $this->fillDynamicData($product);
                }
            }
        }
        return $data;
    }

    /**
     * Fill data column
     *
     * @param ProductModel
     * @return array
     */
    protected function fillDynamicData($product)
    {
        return [
            'id' => $product->getData('product_id'),
            'sku' => $product->getData('product_sku'),
            'name' => $product->getData('product_name'),
            'total_qty' => $product->getData('old_qty') * 1,
            'stocktaking_qty' => $product->getData('stocktaking_qty') * 1,
            'stocktaking_reason' => $product->getData('stocktaking_reason'),
            'image' => $product->getData('image_url'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return parent::modifyMeta($meta);
    }

    /**
     * get visible
     *
     * @param
     * @return
     */
    public function getVisible()
    {
        $requestId = $this->request->getParam('id');
        if ($requestId)
            return $this->_visible;
        return false;
    }

    /**
     * Fill meta columns
     *
     * @return array
     */
    protected function fillModifierMeta()
    {
        $additionalColumns = $this->getAdditionalColumns();
        $modifierColumns = array_replace_recursive(
             [
                 'id' => $this->getTextColumn('id', true, __('ID'), 10),
                 'name' => $this->getTextColumn('name', false, __('Name'), 20),
                 'sku' => $this->getTextColumn('sku', false, __('SKU'), 30),
                 'image' => [
                     'arguments' => [
                         'data' => [
                             'config' => [
                                 'componentType' => Form\Field::NAME,
                                 'formElement' => Form\Element\Input::NAME,
//                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                                 'elementTmpl' => 'Magestore_InventorySuccess/dynamic-rows/cells/thumbnail',
                                 'dataType' => Form\Element\DataType\Media::NAME,
                                 'dataScope' => 'image',
                                 'fit' => __('Thumbnail'),
                                 'label' => __('Thumbnail'),
                                 'sortOrder' => 35,
                                 'visible' => $this->getVisibleImage()
                             ],
                         ],
                     ],
                 ],
            ],
            $additionalColumns
        );
        $modifierColumns = array_replace_recursive(
            $modifierColumns,
            $this->getActionColumns()
        );
        return $modifierColumns;
    }

    /**
     * Fill meta columns
     *
     * @return array
     */
    protected function getAdditionalColumns()
    {
        if($this->getStocktakingStatus() == StocktakingModel::STATUS_COMPLETED)
            return [
                'total_qty' => $this->getTextColumn('total_qty', false, __('Qty in Location'), 40),
                'stocktaking_qty' => $this->getTextColumn('stocktaking_qty', false, __('Counted Qty'), 50),
                'stocktaking_reason' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Number::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'componentType' => Form\Field::NAME,
                                'dataScope' => 'stocktaking_reason',
                                'default' => '',
                                'label' => __('Reason of discrepancy'),
                                'fit' => true,
                                'additionalClasses' => 'admin__field-small',
                                'sortOrder' => 60,
                            ],
                        ],
                    ],
                ],
            ];
        if($this->getStocktakingStatus() == StocktakingModel::STATUS_PROCESSING)
            return [
                'stocktaking_qty' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Number::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'componentType' => Form\Field::NAME,
                                'dataScope' => 'stocktaking_qty',
                                'default' => '',
                                'label' => __('Counted Qty'),
                                'fit' => true,
                                'additionalClasses' => 'admin__field-small',
                                'sortOrder' => 40,
                                'validation' => [
                                    'validate-number' => true,
                                    'validate-not-negative-number' => true,
                                    'required-entry' => true,
                                ],
                            ],
                        ],
                    ],
                ],

                'stocktaking_reason' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Number::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'componentType' => Form\Field::NAME,
                                'dataScope' => 'stocktaking_reason',
                                'default' => '',
                                'label' => __('Reason of discrepancy'),
                                'fit' => true,
                                'additionalClasses' => 'admin__field-small',
                                'sortOrder' => 50,
                            ],
                        ],
                    ],
                ],
            ];
        if($this->getStocktakingStatus() == StocktakingModel::STATUS_VERIFIED)
            return [
                'stocktaking_qty' => $this->getTextColumn('stocktaking_qty', false, __('Counted Qty'), 50),
                'stocktaking_reason' => $this->getTextColumn('stocktaking_reason', false, __('Reason of discrepancy'), 60),
            ];

        return [];
    }

    /**
     * Fill action columns
     *
     * @return array
     */
    protected function getActionColumns()
    {
        if($this->getStocktakingStatus() == StocktakingModel::STATUS_COMPLETED
        || $this->getStocktakingStatus() == StocktakingModel::STATUS_VERIFIED)
            return [
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
        return [
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 60,
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
            ],
        ];
    }
}
