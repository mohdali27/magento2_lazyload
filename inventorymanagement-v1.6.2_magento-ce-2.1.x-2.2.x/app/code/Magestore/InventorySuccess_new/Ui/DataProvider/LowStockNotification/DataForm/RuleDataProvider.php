<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\LowStockNotification\DataForm;

use Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\CollectionFactory;

/**
 * Class DataProvider
 */
class RuleDataProvider extends \Magestore\InventorySuccess\Ui\DataProvider\LowStockNotification\DataForm\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $pageCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Rule $rule */
        foreach ($items as $rule) {
            $rule->load($rule->getId());
            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        return $this->loadedData;
    }


    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'rule_information' => [
                    'rule_id',
                    'rule_name',
                    'description',
                    'status',
                    'from_date',
                    'to_date',
                    'priority',
                    'update_time_type',
                    'specific_month',
                    'specific_day',
                    'specific_time'
            ],
            'conditions' => [
                    'conditions',
                    'lowstock_threshold_type',
                    'lowstock_threshold',
                    'lowstock_threshold_qty',
                    'sales_period',
                    'update_type',
                    'warehouse_ids'
            ],
            'actions' => [
                    'notifier_emails',
                    'warning_message'
            ]
        ];
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
        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\Status $status */
        $status = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\Status'
        );
        $result['status']['options'] = $status->toOptionArray();

        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\UpdateTimeType $updateTimeType */
        $updateTimeType = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\UpdateTimeType'
        );
        $result['update_time_type']['options'] = $updateTimeType->toOptionArray();

        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\SpecificMonth $specificMonth */
        $specificMonth = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\SpecificMonth'
        );
        $result['specific_month']['options'] = $specificMonth->toOptionArray();

        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\SpecificDay $specificDay */
        $specificDay = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\SpecificDay'
        );
        $result['specific_day']['options'] = $specificDay->toOptionArray();

        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\SpecificTime $specificTime */
        $specificTime = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\SpecificTime'
        );
        $result['specific_time']['options'] = $specificTime->toOptionArray();

        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\UpdateType $updateType */
        $updateType = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\UpdateType'
        );
        $result['update_type']['options'] = $updateType->toOptionArray();
        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\Warehouse $warehosue */
        $warehosue = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\Warehouse'
        );
        $result['warehouse_ids']['options'] = $warehosue->toOptionArray();

        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\LowStockThresholdType $lowstockThresholdType */
        $lowstockThresholdType = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\LowStockThresholdType'
        );
        $result['lowstock_threshold_type']['options'] = $lowstockThresholdType->toOptionArray();
        return $result;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta = array_replace_recursive(
            $meta,
            [
                'rule_information' => [
                    'children' => $this->getRuleInformationChildren(),
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Rule Information'),
                                'collapsible' => true,
                                'componentType' => \Magento\Ui\Component\Form\Fieldset::NAME,
                                'sortOrder' => 10,
                                'opened' => true
                            ]
                        ]
                    ]
                ],
                'conditions' => [
                    'children' => $this->getConditionsChildren(),
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Conditions'),
                                'collapsible' => true,
                                'componentType' => \Magento\Ui\Component\Form\Fieldset::NAME,
                                'sortOrder' => 20,
                                'opened' => false,
                                'canShow' => true
                            ]
                        ]
                    ]
                ],
                'actions' => [
                    'children' => $this->getActionsChildren(),
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Actions'),
                                'collapsible' => true,
                                'componentType' => \Magento\Ui\Component\Form\Fieldset::NAME,
                                'sortOrder' => 30,
                                'opened' => false,
                                'canShow' => true
                            ]
                        ]
                    ]
                ]
            ]
        );
        $meta = $this->prepareMeta($meta);
        return $meta;
    }


    /**
     * @return array
     */
    public function getRuleInformationChildren()
    {
        $children = [
            'rule_id' => $this->getField(__('Rule Id'), 'field', false, 'text', 'input', [], ''),
            'rule_name' => $this->getField(__('Rule Name'), 'field', true, 'text', 'input', ['required-entry' => true], ''),
            'description' => $this->getField(__('Description'), 'field', true, 'text', 'textarea', [], ''),
            'status' => $this->getField(__('Status'), 'field', true, 'number', 'select', ['required-entry' => true], ''),
            'from_date' => $this->getField(__('From'), 'field', true, 'text', 'date', ['validate-date' => true], ''),
            'to_date' => $this->getField(__('To'), 'field', true, 'text', 'date', ['validate-date' => true], ''),
            'priority' => $this->getField(__('Priority'), 'field', true, 'text', 'input', [], ''),
            'update_time_type' => $this->getUpdateTimeField(__('Update time'), 'field', true, 'number', 'select', [], ''),
            'specific_month' => $this->getField(__('Select months'), 'field', true, 'number', 'multiselect', ['required-entry' => true], ''),
            'specific_day' => $this->getField(__('Select days'), 'field', true, 'number', 'multiselect', ['required-entry' => true], ''),
            'specific_time' => $this->getField(__('Select hours'), 'field', true, 'number', 'multiselect', ['required-entry' => true], '')
        ];
        return $children;

    }

    /**
     * @return array
     */
    public function getConditionsChildren()
    {
        $children = [
            'rule' => $this->getConditionsContainer(__('Rule Id'), 'container', false, 'container', 'input', [], ''),
            'lowstock_threshold_type' => $this->getLowstockThresholdTypeField(__('Low Stock Threshold Type'), 'field', true, 'number', 'select', ['required-entry' => true], ''),
            'lowstock_threshold' => $this->getField(__('Threshold (days)'), 'field', true, 'number', 'input', ['required-entry' => true, 'validate-number' => true, 'validate-greater-than-zero' => true], __('Set low stock notification threshold per product by day to sell')),
            'lowstock_threshold_qty' => $this->getField(__('Threshold (quantity)'), 'field', true, 'number', 'input', ['required-entry' => true, 'validate-number' => true, 'validate-greater-than-zero' => true], __('Set low stock notification threshold per product by product Qty')),
            'sales_period' => $this->getField(__('Sales Period (days)'), 'field', true, 'number', 'input', ['required-entry' => true, 'validate-number' => true, 'validate-greater-than-zero' => true], __('History time range to get sale data')),
            'update_type' => $this->getUpdateTypeField(__('Notification Scope'), 'field', true, 'number', 'select', [], ''),
            'warehouse_ids' => $this->getField(__('Location(s)'), 'field', true, 'number', 'multiselect', ['required-entry' => true], '')
        ];
        return $children;

    }


    /**
     * @return array
     */
    public function getActionsChildren()
    {
        $children = [
            'notifier_emails' => $this->getField(__('Notifier email list'), 'field', true, 'text', 'textarea', ['validate-emails' => true], ''),
            'warning_message' => $this->getField(__('Warning Message'), 'field', true, 'text', 'textarea', [], '')
        ];
        return $children;

    }

    /**
     * @param $lable
     * @param $componentType
     * @param $visible
     * @param $dataType
     * @param $formElement
     * @param $validation
     * @param $notice
     * @return array
     */
    public function getUpdateTimeField($lable, $componentType, $visible, $dataType, $formElement, $validation, $notice)
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => $lable,
                        'componentType' => $componentType,
                        'visible' => $visible,
                        'dataType' => $dataType,
                        'formElement' => $formElement,
                        'validation' => $validation,
                        'notice' => $notice,
                        'switcherConfig' => [
                            'enabled' => true,
                            'rules' => [
                                '0' => [
                                    'value' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TIME_TYPE_DAILY,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.rule_information.specific_month',
                                            'callback' => 'hide'
                                        ],
                                        '1' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.rule_information.specific_day',
                                            'callback' => 'hide'
                                        ]
                                    ]
                                ],
                                '1' => [
                                    'value' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TIME_TYPE_MONTHLY,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.rule_information.specific_month',
                                            'callback' => 'show'
                                        ],
                                        '1' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.rule_information.specific_day',
                                            'callback' => 'show'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $container;
    }

    /**
     * @param $lable
     * @param $componentType
     * @param $visible
     * @param $dataType
     * @param $formElement
     * @param $validation
     * @param $notice
     * @return array
     */
    public function getLowstockThresholdTypeField($lable, $componentType, $visible, $dataType, $formElement, $validation, $notice)
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => $lable,
                        'componentType' => $componentType,
                        'visible' => $visible,
                        'dataType' => $dataType,
                        'formElement' => $formElement,
                        'validation' => $validation,
                        'notice' => $notice,
                        'switcherConfig' => [
                            'enabled' => true,
                            'rules' => [
                                '0' => [
                                    'value' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.conditions.lowstock_threshold',
                                            'callback' => 'hide'
                                        ],
                                        '1' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.conditions.lowstock_threshold_qty',
                                            'callback' => 'show'
                                        ],
                                        '2' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.conditions.sales_period',
                                            'callback' => 'hide'
                                        ]
                                    ]
                                ],
                                '1' => [
                                    'value' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.conditions.lowstock_threshold',
                                            'callback' => 'show'
                                        ],
                                        '1' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.conditions.lowstock_threshold_qty',
                                            'callback' => 'hide'
                                        ],
                                        '2' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.conditions.sales_period',
                                            'callback' => 'show'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $container;
    }

    /**
     * @param $lable
     * @param $componentType
     * @param $visible
     * @param $dataType
     * @param $formElement
     * @param $validation
     * @param $notice
     * @return array
     */
    public function getConditionsContainer($lable, $componentType, $visible, $dataType, $formElement, $validation, $notice)
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => $componentType,
                        'visible' => $visible,
                        'dataType' => $dataType
                    ]
                ]
            ],
            'children' => [
                'html_content' => [
                    'arguments' => [
                        'data' => [
                            'type' => 'html_content',
                            'name' => 'html_content',
                            'config' => [
                                'componentType' => $componentType,
                                'component' => 'Magento_Ui/js/form/components/html',
                                'content' => \Magento\Framework\App\ObjectManager::getInstance()->create(
                                    'Magestore\InventorySuccess\Block\Adminhtml\LowStockNotification\Rule\Edit\Tab\Conditions'
                                )->toHtml()
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $container;
    }

    /**
     * @param $lable
     * @param $componentType
     * @param $visible
     * @param $dataType
     * @param $formElement
     * @param $validation
     * @param $notice
     * @return array
     */
    public function getUpdateTypeField($lable, $componentType, $visible, $dataType, $formElement, $validation, $notice)
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => $lable,
                        'componentType' => $componentType,
                        'visible' => $visible,
                        'dataType' => $dataType,
                        'formElement' => $formElement,
                        'validation' => $validation,
                        'notice' => $notice,
                        'switcherConfig' => [
                            'enabled' => true,
                            'rules' => [
                                '0' => [
                                    'value' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_BOTH_SYSTEM_AND_WAREHOUSE,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.conditions.warehouse_ids',
                                            'callback' => 'show'
                                        ]
                                    ]
                                ],
                                '1' => [
                                    'value' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_ONLY_SYSTEM,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.conditions.warehouse_ids',
                                            'callback' => 'hide'
                                        ]
                                    ]
                                ],
                                '2' => [
                                    'value' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_ONLY_WAREHOUSE,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form.conditions.warehouse_ids',
                                            'callback' => 'show'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $container;
    }
}
