<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Send\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magestore\InventorySuccess\Model\ResourceModel\TransferStock\CollectionFactory;
use Magestore\InventorySuccess\Model\TransferStockFactory;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form;
use Magento\Framework\Stdlib\ArrayManager;
use Magestore\InventorySuccess\Model\TransferStock;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class General extends \Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Send\Form\Modifier\SendStock
{
    protected $_opened = false;
    protected $_groupLabel = 'General Information';
    protected $_sortOrder = 7;
    protected $_groupContainer = 'general_information';

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
        $transferStock = $this->getCurrentTransfer();
        if ($transferStock) {
            if (isset($transferStock['transferstock_id'])) {
                $this->loadedData[$transferStock['transferstock_id']] = $transferStock;
            } else {
                $this->loadedData[""] = $transferStock;
            }
        }
        return $this->loadedData;
    }

    /**
     * get visible
     *
     * @param
     * @return
     */
    public function getOpened()
    {
        $requestId = $this->request->getParam('id');
        if ($requestId)
            return $this->_opened;
        return true;
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
     * Get current Transfer
     *
     * @return Adjustment
     * @throws NoSuchEntityException
     */
    public function getCurrentTransfer()
    {
        $transferStock = $this->getCurrentTransferStock();
        if ($transferStock->getId()) {
            $transferStock = $transferStock->getData();
        } else {
            $transferStock = $this->_coreRegistry->registry("current_send_stock");
        }
        return $transferStock;
    }

    /**
     * Prepare meta data
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta($meta)
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
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => $this->_sortOrder
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
            'transferstock_code' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            //'disabled' => $this->isDisabledElement(false),
                            'componentType' => 'field',
                            'dataType' => 'string',
                            'formElement' => 'input',
                            'label' => __('Transfer Code'),
                            'elementTmpl' => $this->getElementTmpl('input', false),
                            'sortOrder' => 1,
                            'validation' => [
                                'required-entry' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'source_warehouse_id' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'field',
                            'dataType' => 'string',
                            'formElement' => 'select',
                            'elementTmpl' => $this->getElementTmpl('select', false),
                            'required' => 'true',
                            'label' => __('Source Location'),
                            //'disabled' => $this->isDisabledElement(false),
                            'sortOrder' => 2,
                            'validation' => [
                                'required-entry' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'des_warehouse_id' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'field',
                            'dataType' => 'string',
                            'formElement' => 'select',
                            'elementTmpl' => $this->getElementTmpl('select', false),
                            'required' => 'true',
                            'label' => __('Destination Location'),
                            //'disabled' => $this->isDisabledElement(false),
                            'sortOrder' => 3,
                            'validation' => [
                                'required-entry' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'notifier_emails' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            //'disabled' => $this->isDisabledElement(true),
                            'componentType' => 'field',
                            'dataType' => 'string',
                            'formElement' => 'input',
                            'label' => __('Notifer Emails'),
                            'elementTmpl' => $this->getElementTmpl('input', true),
                            'sortOrder' => 4,
                            'validation' => [
                                'validate-emails' => true
                            ],
                        ],
                    ],
                ],
            ],
            'reason' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            //'disabled' => $this->isDisabledElement(true),
                            'componentType' => 'field',
                            'dataType' => 'string',
                            'formElement' => 'textarea',
                            'required' => 'true',
                            'label' => __('Reason'),
                            'elementTmpl' => $this->getElementTmpl('textarea', true),
                            'sortOrder' => 5,
                            'validation' => [
                                'required-entry' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $children;
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
    public function getAttributesMeta()
    {
        $result = [];
        $result['source_warehouse_id']['componentType'] = Field::NAME;
        $result['source_warehouse_id']['options'] = $this->warehouseSource->toOptionArray(TransferPermission::SEND_STOCK_CREATE);
        $result['des_warehouse_id']['componentType'] = Field::NAME;
        $result['des_warehouse_id']['options'] = $this->warehouseSource->toOptionArray(TransferPermission::ALL_WAREHOUSE_SEND_REQUEST);
        $result = $this->getDefaultMetaData($result);
        return $result;
    }

    /**
     * Category's fields default values
     *
     * @param array $result
     * @return array
     */
    public function getDefaultMetaData($result)
    {
        $result['transferstock_code']['default'] = $this->_transferStockManagement->generateCode();
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
                    'transferstock_code',
                    'source_warehouse_id',
                    'des_warehouse_id',
                    'notifier_emails',
                    'reason'
                ],
        ];
    }

    /**
     * get opened
     *
     * @param
     * @return
     */
    public function getSortOrder()
    {
        if ($this->getTransferStockStatus() != TransferStock::STATUS_COMPLETED)
            return $this->_sortOrder;
        return '7';
    }


}
