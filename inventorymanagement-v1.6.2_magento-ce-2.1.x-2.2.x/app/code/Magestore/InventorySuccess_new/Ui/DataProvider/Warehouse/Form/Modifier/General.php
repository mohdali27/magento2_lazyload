<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory;
use Magestore\InventorySuccess\Model\WarehouseFactory;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Warehouse;
use Magento\Directory\Model\Config\Source\Country as SourceCountry;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Store\Ui\Component\Listing\Column\Store\Options as StoreOptions;
use Magestore\InventorySuccess\Helper\Data as InventoryHelper;

/**
 * Class General
 * @package Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Modifier
 */
class General extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    protected $collection;

    /**
     * @var WarehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $_warehouseSource;

    /**
     * @var SourceCountry
     */
    protected $sourceCountry;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse
     */
    protected $_currentWarehouse;

    /**
     * @var array
     */
    protected $countries;

    /**
     * @var array
     */
    protected $regions;

    /**
     * @var array
     */
    protected $stores = array();

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var StoreOptions
     */
    protected $storeOption;

    /**
     * @var InventoryHelper
     */
    protected $inventoryHelper;

    /**
     * @var \Magestore\InventorySuccess\Model\Permission\PermissionManagement
     */
    protected $_permissionManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap\CollectionFactory
     */
    protected $warehouseStoreViewMapCollectionFactory;

    protected $_opened = true;
    protected $_groupLabel = 'General Information';
    protected $_sortOrder = 10;
    protected $_groupContainer = 'general_information';
    protected $_eventManager;

    /**
     * General constructor.
     * @param CollectionFactory $collectionFactory
     * @param WarehouseFactory $warehouseFactory
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource
     * @param array $_modifierConfig
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        WarehouseFactory $warehouseFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        SourceCountry $sourceCountry,
        \Magento\Framework\Registry $registry,
        DirectoryHelper $directoryHelper,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        StoreOptions $storeOption,
        InventoryHelper $inventoryHelper,
        \Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap\CollectionFactory $warehouseStoreViewMapCollectionFactory,
        array $_modifierConfig = []
    )
    {
        parent::__construct($urlBuilder, $request, $_modifierConfig);
        $this->collection = $collectionFactory->create();
        $this->_warehouseFactory = $warehouseFactory;
        $this->_warehouseSource = $warehouseSource;
        $this->sourceCountry = $sourceCountry;
        $this->_coreRegistry = $registry;
        $this->directoryHelper = $directoryHelper;
        $this->dataPersistor = $dataPersistor;
        $this->_eventManager = $eventManager;
        $this->_permissionManagement = $permissionManagement;
        $this->storeOption = $storeOption;
        $this->inventoryHelper = $inventoryHelper;
        $this->warehouseStoreViewMapCollectionFactory = $warehouseStoreViewMapCollectionFactory;
    }

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
        $warehouse = $this->getCurrentWarehouse();
        if ($warehouse && $warehouse->getWarehouseId()) {
            $warehouseData = $warehouse->getData();
            $this->loadedData[$warehouse->getId()] = $warehouseData;
        }
        if ($data = $this->dataPersistor->get('inventorysuccess_warehouse')) {
            $this->loadedData[$warehouse->getId()] = $data;
            $this->dataPersistor->clear('inventorysuccess_warehouse');
        }
        $this->loadedData[$warehouse->getId()]['store_ids'] = $this->warehouseStoreViewMapCollectionFactory->create()
            ->addFieldToFilter('warehouse_id', $warehouse->getWarehouseId())
            ->getColumnValues('store_id');
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
        $warehouseId = $this->getCurrentWarehouse()->getWarehouseId();
        if (!$warehouseId)
            return $this->_opened;
        return false;
    }


    /**
     * modify data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        return array_replace_recursive(
            $data,
            $this->getData()
        );
    }

    /**
     * Get current warehouse
     *
     * @return Adjustment
     * @throws NoSuchEntityException
     */
    public function getCurrentWarehouse()
    {
        if (!$this->_currentWarehouse)
            $this->_currentWarehouse = $this->_coreRegistry->registry('current_warehouse');
        return $this->_currentWarehouse;
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
                                'sortOrder' => $this->_sortOrder,
                                'formSubmitType' => 'ajax',
                            ],
                        ],
                    ],
                ],
            ]
        );
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
            'messages' => $this->addMessageField(),
            'warehouse_name' => $this->addWarehouseFields('Location Name', 'input', 20, true),
            'warehouse_code' => $this->addWarehouseFields('Location Code', 'input', 30, true),
            'contact_email' => $this->addWarehouseFields('Contact Email', 'input', 40),
            'telephone' => $this->addWarehouseFields('Telephone', 'input', 50),
            'street' => $this->addWarehouseFields('Street', 'input', 60, true),
            'city' => $this->addWarehouseFields('City', 'input', 70, true),
            'country_id' => $this->getCountryIdField(),
            'region' => $this->getRegionField(),
            'region_id' => $this->getRegionIdField(),
            'postcode' => $this->addWarehouseFields('Zip/Postal Code', 'input', 110, true),
        ];

        /* add store view field to general form */
        if ($this->inventoryHelper->getLinkWarehouseStoreConfig()) {
            $children['store_ids'] = $this->getStoreIdsField();
        }

//        if(!$this->getCurrentWarehouse()->getIsPrimary())
//            $children['status'] = $this->getStatusField();
        $warehouse = $this->getCurrentWarehouse();
        if ($warehouse && $warehouse->getWarehouseId()) {
            $children['warehouse_id'] = $this->addWarehouseFields('', 'hidden', 10);
            if ($this->_permissionManagement->checkPermission(
                'Magestore_InventorySuccess::warehouse_edit',
                $this->getCurrentWarehouse()
            )
            ) {
                $children['warehouse_button_set'] = $this->getCustomButtons();
            }
        }
        $fieldSet = new \Magento\Framework\DataObject();
        $fieldSet->setData($children);
        $this->_eventManager->dispatch('inventorysuccess_warehouse_edit_form', ['form' => $this, 'field_set' => $fieldSet, 'model_data' => $warehouse]);
        $children = $fieldSet->getData();
        return $children;
    }

    protected function addMessageField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => \Magento\Ui\Component\Container::NAME,
                        'component' => 'Magento_Catalog/js/components/messages'
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getCustomButtons()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
//                        'content' => __($this->_fieldsetContent),
                        'template' => 'Magestore_InventorySuccess/form/components/button-list',
                        'sortOrder' => 5,
                    ],
                ],
            ],
            'children' => [
                'save_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => 'os_warehouse_form.os_warehouse_form',
                                        'actionName' => 'save',
                                        'params' => [
                                            true,
                                            [
                                                'back' => 'edit'
                                            ]
                                        ]
                                    ]
                                ],
                                'title' => __('Save General Information'),
                                'provider' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $label
     * @param string $formElement
     * @param int $sortOrder
     * @param bool $validation
     * @return array
     */
    protected function addWarehouseFields($label, $formElement, $sortOrder, $validation = false)
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'label' => __($label),
                        'dataType' => 'text',
                        'formElement' => $formElement,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
        if ($validation)
            $field['arguments']['data']['config']['validation'] = ['required-entry' => true];
        return $field;
    }

    /**
     * Get country id field
     *
     * @return array
     */
    protected function getCountryIdField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'label' => __('Country'),
                        'dataType' => 'text',
                        'formElement' => 'select',
                        'options' => $this->getCountries(),
                        'dataScope' => 'country_id',
                        'sortOrder' => 80,
                        'validation' => ['required-entry' => true]
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Get country id field
     *
     * @return array
     */
    protected function getRegionField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => false,
                        'componentType' => Field::NAME,
                        'label' => __('State or Province'),
                        'dataType' => 'text',
                        'formElement' => 'input',
                        'dataScope' => 'region',
                        'sortOrder' => 100,
                        'validation' => ['required-entry' => true]
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Get country id field
     *
     * @return array
     */
    protected function getRegionIdField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'select',
                        'component' => 'Magestore_InventorySuccess/js/form/element/region',
                        'elementTmpl' => 'ui/form/element/select',
                        'customEntry' => 'region',
                        'componentType' => Field::NAME,
                        'label' => __('State or Province'),
                        'dataType' => 'text',
                        'options' => $this->getRegions(),
                        'dataScope' => 'region_id',
                        'sortOrder' => 90,
                        'filterBy' => [
                            'target' => 'os_warehouse_form.os_warehouse_form.general_information.country_id',
                            'field' => 'country',
                        ],
                        'validation' => ['required-entry' => true]
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Get country id field
     *
     * @return array
     */
    protected function getStatusField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'select',
                        'componentType' => Field::NAME,
                        'label' => __('Status'),
                        'options' => \Magestore\InventorySuccess\Model\Warehouse\Options\Status::getOptionArray(),
                        'dataType' => 'text',
                        'dataScope' => 'status',
                        'sortOrder' => 120,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Get store view field
     *
     * @return array
     */
    protected function getStoreIdsField()
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'label' => __('Magento Store View'),
                        'dataType' => 'text',
                        'formElement' => 'multiselect',
                        'options' => $this->getStoreViews(),
                        'dataScope' => 'store_ids',
                        'sortOrder' => 130
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     *
     * @return array
     */
    protected function getStoreViews()
    {
        if (!count($this->stores)) {
            $this->stores = array_merge_recursive($this->stores, $this->storeOption->toOptionArray());
        }
        return $this->stores;
    }

    /**
     * Retrieve countries
     *
     * @return array|null
     */
    protected function getCountries()
    {
        if (null === $this->countries) {
            $this->countries = $this->sourceCountry->toOptionArray();
        }

        return $this->countries;
    }

    /**
     * Retrieve regions
     *
     * @return array
     */
    protected function getRegions()
    {
        if (null === $this->regions) {
            $regions = $this->directoryHelper->getRegionData();
            $this->regions = [];

            unset($regions['config']);

            foreach ($regions as $countryCode => $countryRegions) {
                foreach ($countryRegions as $regionId => $regionData) {
                    $this->regions[] = [
                        'label' => $regionData['name'],
                        'value' => $regionId,
                        'country' => $countryCode,
                    ];
                }
            }
        }

        return $this->regions;

    }
}