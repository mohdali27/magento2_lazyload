<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\Stocktaking\Form\Modifier;

use Magento\Ui\Component\Form\Field;
use Magestore\InventorySuccess\Model\Stocktaking as StocktakingModel;
use Magestore\InventorySuccess\Model\StocktakingFactory;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class General extends \Magestore\InventorySuccess\Ui\DataProvider\Stocktaking\Form\Modifier\Stocktaking
{

    /**
     * @var bool
     */
    protected $_opened = false;
    /**
     * @var string
     */
    protected $_groupContainer = 'general_information';
    /**
     * @var string
     */
    protected $_groupLabel = 'General Information';
    /**
     * @var int
     */
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
        $stocktaking = $this->getCurrentStocktaking();
        if ($stocktaking && $stocktaking->getId()) {
            $stocktakingData = $stocktaking->getData();
            $this->loadedData[$stocktaking->getId()] = $stocktakingData;
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
        if ($requestId && $this->getStocktakingStatus() != StocktakingModel::STATUS_COMPLETED)
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
        if ($this->getStocktakingStatus() != StocktakingModel::STATUS_COMPLETED)
            return $this->_sortOrder;
        return '5';
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
            'warehouse_id' => $this->getWarehouseIdField(),
            'stocktaking_code' => $this->getStocktakingCodeField(),
            'reason' => $this->getReasonField(),
            'participants' => $this->getParticipantsField(),
            'stocktake_at' => $this->getStocktakeAtField(),
        ];
        if($this->getStocktakingStatus() == StocktakingModel::STATUS_COMPLETED)
            $children = [
                'warehouse_id' => $this->getWarehouseIdField(),
                'stocktaking_code' => $this->getStocktakingCodeField(),
                'participants' => $this->getParticipantsField(),
                'status' => $this->getStocktakingStatusField(),
                'stocktake_at' => $this->getStocktakeAtField(),
                'created_by' => $this->getStocktakingCreatedByField(),
                'created_at' => $this->getStocktakingCreatedAtField(),
                'verified_by' => $this->getVerifiedByField(),
                'confirmed_by' => $this->getConfirmedByField(),
                'reason' => $this->getReasonField(),
            ];
        return $children;
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
                        'required' =>'true',
                        'label' =>__('Location'),
                        'elementTmpl' => $this->getModifyTmpl('select'),
//                        'disabled' => $this->isDisabledElement(),
                        'sortOrder' => 10,
                        'validation' => [
                            'required-entry' => true,
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
    protected function getStocktakingCodeField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'input',
                        'elementTmpl' => $this->getModifyTmpl('input'),
                        'required' =>'true',
                        'label' =>__('Stocktaking Code'),
                        'sortOrder' => 15,
                        'validation' => [
                            'required-entry' => true,
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
    protected function getParticipantsField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'input',
                        'elementTmpl' => $this->getModifyTmpl('input'),
                        'required' =>false,
                        'label' =>__('Participants'),
                        'sortOrder' => 20,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Stocktaking time field
     *
     * @return array
     */
    protected function getStocktakeAtField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'date',
                        'additionalClasses' => 'admin__field-date',
                        'formElement' =>'date',
                        'elementTmpl' => $this->getModifyTmpl('date'),
                        'required' =>false,
                        'label' =>__('Stocktaking Time'),
                        'sortOrder' => 30,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Stocktaking code field
     *
     * @return array
     */
    protected function getStocktakingStatusField()
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
                        'sortOrder' =>40,
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
    protected function getStocktakingCreatedByField()
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
                        'sortOrder' => 50,
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
    protected function getStocktakingCreatedAtField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'date',
                        'elementTmpl' => $this->getModifyTmpl('date'),
                        'label' =>__('Created At'),
                        'sortOrder' => 60,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Verified by field
     *
     * @return array
     */
    protected function getVerifiedByField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'input',
                        'elementTmpl' => $this->getModifyTmpl('input'),
                        'label' =>__('Verified By'),
                        'sortOrder' => 70,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Confirmed by field
     *
     * @return array
     */
    protected function getConfirmedByField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'dataType' => 'string',
                        'formElement' =>'input',
                        'elementTmpl' => $this->getModifyTmpl('input'),
                        'label' =>__('Confirmed By'),
                        'sortOrder' => 80,
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
                        'required' =>'true',
                        'label' =>__('Reason'),
                        'sortOrder' => 90,
                        'validation' => [
                            'required-entry' => true,
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
        $result['warehouse_id']['options'] = $this->warehouseSource->toOptionArray('Magestore_InventorySuccess::create_stocktaking');
        if($this->getStocktakingStatus() == StocktakingModel::STATUS_COMPLETED) {
            $result['status']['componentType'] = Field::NAME;
            $result['status']['options'] = $this->stocktakingStatus->toOptionArray();
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
        $generatedCode = $this->stocktakingManagement->generateCode();
        $result['stocktaking_code']['default'] = $generatedCode;
//        $result['stocktake_at']['default'] = $this->date->date('Y-m-d H:i');
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
                    'warehouse_id',
                    'stocktaking_code',
                    'participants',
                    'stocktake_at',
                    'status',
                    'created_by',
                    'created_at',
                    'verified_by',
                    'confirmed_by',
                    'reason',
                ],
        ];
    }
}
