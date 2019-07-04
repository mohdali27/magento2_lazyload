<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\SupplyNeeds\DataForm;

use Magento\Backend\Block\Widget\Grid\Column\Filter\Date;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class DataProvider
 */
class SupplyNeedsDataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    protected $_context;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

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
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        ContextInterface $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        array $meta = [],
        array $data = []
    ) {
        $this->_context = $context;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->localeDate = $localeDate;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $this->loadedData = [];
        $topFilter = $this->request->getParam('top_filter');
        $sort = $this->request->getParam('sort');
        $dir = $this->request->getParam('dir');
        /** @var \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $locator = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\Locator\LocatorFactory'
        )->create();
        $supplyNeedsTopFilter = [
            'top_filter' => $topFilter,
            'sort' => $sort,
            'dir' => $dir,
        ];
        $locator->setSesionByKey('supply_needs_top_filter', $supplyNeedsTopFilter);
        if (!empty($topFilter)) {
            $data = unserialize(base64_decode($topFilter));
            if ($data['from_date']) {
                $data['from_date'] = date('Y-m-d', strtotime($data['from_date']));
            }
            if ($data['to_date']) {
                $data['to_date'] = date('Y-m-d', strtotime($data['to_date']));
            }
            if ($data['forecast_date_to']) {
                $data['forecast_date_to'] = date('Y-m-d', strtotime($data['forecast_date_to']));
            }
            $this->loadedData[1] = $data;
        }
        return $this->loadedData;
    }


    /**
     * Prepare meta data
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta($meta)
    {
        $meta = array_replace_recursive(
            $meta,
            $this->prepareFieldsMeta(
                $this->getFieldsMap(),
                $this->getAttributesMeta()
            )
        );

        return $meta;
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
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'criteria' =>
                [
                    'warehouse_ids',
                    'sales_period',
                    'from_date',
                    'to_date',
                    'forecast_date_to'
                ],
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
        /** @var \Magestore\InventorySuccess\Model\SupplyNeeds\Source\Warehouse $warehouseSource */
        $warehouseSource = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\SupplyNeeds\Source\Warehouse'
        );
        $result['warehouse_ids']['options'] = $warehouseSource->toOptionArray();
        /** @var \Magestore\InventorySuccess\Model\SupplyNeeds\Source\SalesPeriod $salesPeriod */
        $salesPeriod = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\SupplyNeeds\Source\SalesPeriod'
        );
        $result['sales_period']['options'] = $salesPeriod->toOptionArray();
        return $result;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $topFilter = $this->_context->getRequestParam('top_filter', false);
        $opened = false;
        if (!$topFilter)
            $opened = true;
        $meta = array_replace_recursive(
            $meta,
            [
                'criteria' => [
                    'children' => $this->getChildren(),
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Select criteria to forecast supply needs'),
                                'collapsible' => true,
                                'componentType' => \Magento\Ui\Component\Form\Fieldset::NAME,
                                'sortOrder' => 10,
                                'opened' => $opened
                            ],
                        ],
                    ],
                ],
            ]
        );
        $meta = $this->prepareMeta($meta);
        return $meta;
    }


    public function getChildren()
    {
        $children = [
            'warehouse_ids' => $this->getWarehouseIdsField(),
            'sales_period' => $this->getSalesPeriodField(),
            'from_date' => $this->getFromDateField(),
            'to_date' => $this->getToDateField(),
            'forecast_date_to' => $this->getForecastDateToField()
        ];
        return $children;

    }

    /**
     * @return array
     */
    public function getWarehouseIdsField()
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Location(s)'),
                        'componentType' => 'field',
                        'dataType' => 'text',
                        'formElement' => 'multiselect',
                        'validation' => [
                            'required-entry' => true
                        ],
                        'notice' => __('Location to calculate supply needs')
                    ]
                ]
            ]
        ];
        return $container;
    }

    /**
     * @return array
     */
    public function getSalesPeriodField()
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Sales Period'),
                        'componentType' => 'field',
                        'dataType' => 'text',
                        'formElement' => 'select',
                        'validation' => [
                            'required-entry' => true
                        ],
                        'notice' => __('History time range to get sales data'),
                        'switcherConfig' => [
                            'enabled' => true,
                            'rules' => [
                                '0' => [
                                    'value' => \Magestore\InventorySuccess\Model\SupplyNeeds::SALES_PERIOD_LAST_7_DAYS,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_supplyneeds_form.os_supplyneeds_form.criteria.from_date',
                                            'callback' => 'hide'
                                        ],
                                        '1' => [
                                            'target' => 'os_supplyneeds_form.os_supplyneeds_form.criteria.to_date',
                                            'callback' => 'hide'
                                        ]
                                    ]
                                ],
                                '1' => [
                                    'value' => \Magestore\InventorySuccess\Model\SupplyNeeds::SALES_PERIOD_LAST_30_DAYS,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_supplyneeds_form.os_supplyneeds_form.criteria.from_date',
                                            'callback' => 'hide'
                                        ],
                                        '1' => [
                                            'target' => 'os_supplyneeds_form.os_supplyneeds_form.criteria.to_date',
                                            'callback' => 'hide'
                                        ]
                                    ]
                                ],
                                '2' => [
                                    'value' => \Magestore\InventorySuccess\Model\SupplyNeeds::SALES_PERIOD_3_MONTHS,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_supplyneeds_form.os_supplyneeds_form.criteria.from_date',
                                            'callback' => 'hide'
                                        ],
                                        '1' => [
                                            'target' => 'os_supplyneeds_form.os_supplyneeds_form.criteria.to_date',
                                            'callback' => 'hide'
                                        ]
                                    ]
                                ],
                                '3' => [
                                    'value' => \Magestore\InventorySuccess\Model\SupplyNeeds::CUSTOM_RANGE,
                                    'actions' => [
                                        '0' => [
                                            'target' => 'os_supplyneeds_form.os_supplyneeds_form.criteria.from_date',
                                            'callback' => 'show'
                                        ],
                                        '1' => [
                                            'target' => 'os_supplyneeds_form.os_supplyneeds_form.criteria.to_date',
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

    public function getFromDateField()
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('From'),
                        'componentType' => 'field',
                        'dataType' => 'text',
                        'formElement' => 'date',
                        'validation' => [
                            'required-entry' => true,
                            'validate-date' => true
                        ],
                        'options' => [
                            'maxDate' => $this->localeDate->formatDate(),
                        ]
                    ]
                ]
            ]
        ];
        return $container;
    }

    public function getToDateField()
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('To'),
                        'componentType' => 'field',
                        'dataType' => 'text',
                        'formElement' => 'date',
                        'validation' => [
                            'required-entry' => true,
                            'validate-date' => true
                        ],
                        'options' => [
                            'maxDate' => $this->localeDate->formatDate(),
                        ]
                    ]
                ]
            ]
        ];
        return $container;
    }

    public function getForecastDateToField()
    {
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Stdlib\DateTime\DateTime'
        );
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Forecast Supply Needs To'),
                        'componentType' => 'field',
                        'dataType' => 'text',
                        'formElement' => 'date',
                        'validation' => [
                            'required-entry' => true,
                            'validate-date' => true
                        ],
                        'notice' => __('Future time point to calculate supply needs'),
                        'options' => [
                            'minDate' => $this->localeDate->formatDate(),
                        ]
                    ]
                ]
            ]
        ];
        return $container;
    }
}
