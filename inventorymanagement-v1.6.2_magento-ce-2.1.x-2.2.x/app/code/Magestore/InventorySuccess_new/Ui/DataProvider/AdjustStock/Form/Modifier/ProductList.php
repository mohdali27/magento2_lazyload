<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\AdjustStock\Form\Modifier;

use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductList extends \Magestore\InventorySuccess\Ui\DataProvider\AdjustStock\Form\Modifier\AdjustStock
{

    /**
     * @var string
     */
    protected $_groupContainer = 'os_adjuststock';

    /**
     * @var string
     */
    protected $_dataLinks = 'product_list';

    /**
     * @var string
     */
    protected $_fieldsetContent = 'Please add or import products to adjust stock';

    /**
     * @var string
     */
    protected $_buttonTitle = 'Add Products to Adjust Stock';

    /**
     * @var string
     */
    protected $_modalTitle = 'Add Products to Adjust Stock';

    /**
     * @var string
     */
    protected $_modalDataId = 'adjuststock_id';

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
        'listing' => 'os_adjuststock_product_listing',
        'form' => 'os_adjuststock_form',
        'columns_ids' => 'product_columns.ids'
    ];

    /**
     * @var array
     */
    protected $_mapFields = [
        'id' => 'entity_id',
        'sku' => 'sku',
        'name' => 'name',
        'total_qty' => 'total_qty',
        'change_qty' => 'change_qty',
        'adjust_qty' => 'adjust_qty',
        'image' => 'image_url',
    ];

    /**
     * get fieldset content
     *
     * @param
     * @return
     */
    public function getFieldsetContent(){
        if ($this->getAdjustStockStatus() != '1')
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
        if ($this->getAdjustStockStatus() != '1')
            return $this->_useButtonSet;
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
//        return parent::modifyData($data);
        $modelId = $this->request->getParam('id');
        if ($modelId) {
            $products = $this->collection->getAdjustedProducts($modelId);
            $data[$modelId]['links'][$this->_dataLinks] = [];
            if($products->getSize() > 0) {
                foreach ($products as $product) {
                    $data[$modelId]['links'][$this->_dataLinks][] = $this->fillDynamicData($product);
                }
            }
//                $data[$modelId]['links'][$this->_dataLinks] = $products->getData();
        }
//            $data[$modelId]['links']['product_stock_modal']['config']['update_url'] = 'aaa';
//        \Zend_Debug::dump($data);
//        die();
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
            'change_qty' => $product->getData('change_qty') * 1,
            'adjust_qty' => $product->getData('adjust_qty') * 1,
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
     * get modal title
     *
     * @param
     * @return
     */
    public function getImportTitle(){
        if($this->getAdjustStockStatus() == AdjustStockInterface::STATUS_PROCESSING)
            return 'Import products adjust stock';
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
                'total_qty' => $this->getTextColumn('total_qty', false, __('Old Qty'), 40),
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
                                'sortOrder' => 45,
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
        if($this->getAdjustStockStatus() == AdjustStockInterface::STATUS_COMPLETED)
            return [
                'change_qty' => $this->getTextColumn('change_qty', false, __('Change Qty'), 50),
                'adjust_qty' => $this->getTextColumn('adjust_qty', false, __('Adjust Qty'), 55),
            ];
        $adjustStockColumnKey = 'adjust_qty';
        $adjustStockColumnLabel = 'Adjust Qty';
        $negativeValidate = true;
        if($this->helper->getAdjustStockChange()){
            $adjustStockColumnKey = 'change_qty';
            $adjustStockColumnLabel = 'Change Qty';
            $negativeValidate = false;
        }
        return [
            $adjustStockColumnKey => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => $adjustStockColumnKey,
                            'label' => __($adjustStockColumnLabel),
                            'fit' => true,
                            'additionalClasses' => 'admin__field-small',
                            'sortOrder' => 50,
                            'validation' => [
                                'validate-number' => true,
                                'validate-not-negative-number' => $negativeValidate,
                                'required-entry' => true,
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }

    /**
     * Fill action columns
     *
     * @return array
     */
    protected function getActionColumns()
    {
        if ($this->getAdjustStockStatus() == AdjustStockInterface::STATUS_COMPLETED)
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
