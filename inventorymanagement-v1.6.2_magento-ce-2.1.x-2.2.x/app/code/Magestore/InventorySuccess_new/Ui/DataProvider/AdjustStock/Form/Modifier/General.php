<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\AdjustStock\Form\Modifier;

use Magento\Ui\Component\Form\Field;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class General extends \Magestore\InventorySuccess\Ui\DataProvider\AdjustStock\Form\Modifier\AdjustStock
{

    protected $_opened = false;
    protected $_groupContainer = 'general_information';
    protected $_groupLabel = 'General Information';
    protected $_sortOrder = 100;

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $this->loadedData = [];
        $adjustStock = $this->getCurrentAdjustment();
        if ($adjustStock && $adjustStock->getId()) {
            $adjustStockData = $adjustStock->getData();
            $this->loadedData[$adjustStock->getId()] = $adjustStockData;
        }
        return $this->loadedData;
    }

    /**
     * get opened
     *
     * @param
     * @return
     */
    public function getOpened()
    {
        $requestId = $this->request->getParam('id');
        if ($requestId && $this->getAdjustStockStatus() != AdjustStockInterface::STATUS_COMPLETED)
            return $this->_opened;
        return true;
    }

    /**
     * get opened
     *
     * @param
     * @return
     */
    public function getSortOrder()
    {
        if ($this->getAdjustStockStatus() != AdjustStockInterface::STATUS_COMPLETED)
            return $this->_sortOrder;
        return '5';
    }
    
    /**
     * get is required
     *
     * @param
     * @return boolean
     */
    public function getIsRequired()
    {
        if ($this->getAdjustStockStatus() != AdjustStockInterface::STATUS_COMPLETED)
            return $this->_sortOrder;
        return false;
    }

    /**
     * modify data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        $data = array_replace_recursive(
            $data,
            $this->getData()
        );
        return $data;
    }

    /**
     * Prepare meta data
     *
     * @param array $meta
     * @return array
     */
    protected function prepareMeta($meta)
    {
        $meta = array_replace_recursive($meta, $this->prepareFieldsMeta(
            $this->getFieldsMap(),
            $this->getAttributesMeta()
        ));

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                $this->_groupContainer => [
                    'children' => $this->getGeneralChildren(),
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __($this->getGroupLabel()),
                                'collapsible' => $this->getCollapsible(),
                                'dataScope' => 'data',
                                'visible' => $this->getVisible(),
                                'opened' => $this->getOpened(),
                                'componentType' => 'fieldset',
                                'sortOrder' => $this->getSortOrder()
                            ],
                        ],
                    ],
                ],
            ]
        );
        $meta = $this->prepareMeta($meta);
        return $meta;
    }

    /**
     * Retrieve child meta configuration
     *
     * @return array
     */
    protected function getGeneralChildren()
    {
        $children = [
            'adjuststock_code' => $this->getAdjustStockCodeField(),
            'warehouse_id' => $this->getWarehouseIdField(),
            'reason' => $this->getReasonField(),
        ];

        if($this->getAdjustStockStatus() == AdjustStockInterface::STATUS_COMPLETED)
            $children = [
                'adjuststock_code' => $this->getAdjustStockCodeField(),
                'warehouse_id' => $this->getWarehouseIdField(),
                'status' => $this->getStatusField(),
                'created_by' => $this->getCreatedByField(),
                'created_at' => $this->getCreatedAtField(),
                'confirmed_by' => $this->getAdjustedByField(),
                'reason' => $this->getReasonField(),
            ];
        return $children;
    }

    /**
     * Adjust stock field
     *
     * @return array
     */
    protected function getAdjustStockCodeField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'input',
                        'elementTmpl' => $this->getModifyTmpl('input'),
                        'required' => $this->getIsRequired(),
                        'label' =>__('Adjustment Code'),
                        'sortOrder' => 20,
                        'validation' => [
                            'required-entry' => $this->getIsRequired(),
                        ],
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Adjust stock field
     *
     * @return array
     */
    protected function getWarehouseIdField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'select',
                        'label' =>__('Location'),
                        'elementTmpl' => $this->getModifyTmpl('select'),
                        'sortOrder' => 10,
                        'required' => $this->getIsRequired(),
                        'validation' => [
                            'required-entry' => $this->getIsRequired(),
                        ],
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Adjust stock field
     *
     * @return array
     */
    protected function getStatusField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'select',
                        'label' =>__('Status'),
                        'elementTmpl' => $this->getModifyTmpl('select'),
                        'sortOrder' =>25,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Created by field
     *
     * @return array
     */
    protected function getCreatedByField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'input',
                        'elementTmpl' => $this->getModifyTmpl('input'),
                        'label' =>__('Created By'),
                        'sortOrder' => 30,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Created at field
     *
     * @return array
     */
    protected function getCreatedAtField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'input',
                        'elementTmpl' => $this->getModifyTmpl('input'),
                        'label' =>__('Created At'),
                        'sortOrder' => 40,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Created at field
     *
     * @return array
     */
    protected function getAdjustedByField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'input',
                        'elementTmpl' => $this->getModifyTmpl('input'),
                        'label' =>__('Adjusted By'),
                        'sortOrder' => 45,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Reason field
     *
     * @return array
     */
    protected function getReasonField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' => 'textarea',
                        'elementTmpl' => $this->getModifyTmpl('textarea'),
                        'required' => $this->getIsRequired(),
                        'label' =>__('Reason'),
                        'sortOrder' => 50,
                        'validation' => [
                            'required-entry' => $this->getIsRequired(),
                        ],
                    ],
                ],
            ],
        ];
        return $field;
    }



    /**
     * Prepare fields meta based on xml declaration of form and fields metadata
     *
     * @param array $fieldsMap
     * @param array $fieldsMeta
     * @return array
     */
    private function prepareFieldsMeta($fieldsMap, $fieldsMeta)
    {
        $result = [];
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $fieldsMeta[$field];
                }
            }
        }
        return $result;
    }

    /**
     * Get attributes meta
     *
     * @param
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getAttributesMeta()
    {
        $result = [];
        $result['warehouse_id']['componentType'] = Field::NAME;
        $result['warehouse_id']['options'] = $this->warehouseSource->toOptionArray('Magestore_InventorySuccess::create_adjuststock');
        if($this->getAdjustStockStatus() == AdjustStockInterface::STATUS_COMPLETED) {
            $result['status']['componentType'] = Field::NAME;
            $result['status']['options'] = $this->adjustStockStatus->toOptionArray();
        }
        $result = $this->getDefaultMetaData($result);
        return $result;
    }

    /**
     * Category's fields default values
     *
     * @param array $result
     * @return array
     */
    protected function getDefaultMetaData($result)
    {
        $generatedCode = $this->adjustStockManagement->generateCode();
        $result['adjuststock_code']['default'] = $generatedCode;
        return $result;
    }

    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general_information' =>
                [
                    'adjuststock_code',
                    'warehouse_id',
                    'status',
                    'created_by',
                    'created_at',
                    'confirmed_by',
                    'reason',
                ],
        ];
    }
}
