<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\External\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class General extends \Magestore\InventorySuccess\Ui\DataProvider\TransferStock\External\Form\Modifier\ExternalStock
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $_warehouseSource;

    protected $_opened = false;
    protected $_groupLabel = 'General Information';
    protected $_sortOrder = 100;
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
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCurrentTransfer()
    {
        $transferStock = $this->getCurrentTransferStock();
        if ($transferStock->getId()) {
            $transferStock = $transferStock->getData();
        } else {
            $transferStock = $this->_coreRegistry->registry("current_external_transfer_stock");
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
                                'label' => __($this->_groupLabel),
                                'collapsible' => true,
                                'dataScope' => 'data',
                                'visible' => $this->_visible,
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

    protected function getWarehouseCode()
    {
        $warehouse_code = 'source_warehouse_id';
        $type = $this->request->getParam('type');
        if ($type == 'from_external') {
            $warehouse_code = 'des_warehouse_id';
        }
        return $warehouse_code;
    }

    /**
     * Retrieve child meta configuration
     *
     * @return array
     */
    protected function getGeneralChildren()
    {
        $warehouse_code = $this->getWarehouseCode();

        $children = [
            'transferstock_code' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'field',
                            'dataType' => 'string',
                            'formElement' => 'input',
                            'label' => __('Transfer Code'),
                            'elementTmpl' => $this->getElementTmpl('input', false),
                            'sortOrder' => 10,
                            'validation' => [
                                'required-entry' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'external_location' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            //'disabled' => $this->isDisabledElement(),
                            'componentType' => 'field',
                            'dataType' => 'string',
                            'formElement' => 'input',
                            'elementTmpl' => $this->getElementTmpl('input', false),
                            'required' => 'true',
                            'label' => __('External Location'),
                            'sortOrder' => 20,
                            'validation' => [
                                'required-entry' => true,
                            ],
                        ],
                    ],
                ],
            ],
            $warehouse_code => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            //'disabled' => $this->isDisabledElement(),
                            'componentType' => 'field',
                            'elementTmpl' => $this->getElementTmpl('select', false),
                            'dataType' => 'string',
                            'formElement' => 'select',
                            'required' => 'true',
                            'label' => __('Location'),
                            'sortOrder' => 30,
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
                            //'disabled' => $this->isDisabledElement(),
                            'elementTmpl' => $this->getElementTmpl('input', true),
                            'componentType' => 'field',
                            'dataType' => 'string',
                            'formElement' => 'input',
                            'label' => __('Notifer Emails'),
                            'sortOrder' => 40,
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
                            //'disabled' => $this->isDisabledElement(),
                            'componentType' => 'field',
                            'dataType' => 'string',
                            'formElement' => 'textarea',
                            'elementTmpl' => $this->getElementTmpl('textarea', true),
                            'required' => 'true',
                            'label' => __('Reason'),
                            'sortOrder' => 50,
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
        $warehouse_code = $this->getWarehouseCode();
        $result[$warehouse_code]['componentType'] = Field::NAME;
        $result[$warehouse_code]['options'] = $this->_warehouseSource->toOptionArray();

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
        $warehouse_code = $this->getWarehouseCode();
        return [
            'general_information' =>
                [
                    'transferstock_code',
                    'external_location',
                    $warehouse_code,
                    'notifier_emails',
                    'reason'
                ],
        ];
    }
}
