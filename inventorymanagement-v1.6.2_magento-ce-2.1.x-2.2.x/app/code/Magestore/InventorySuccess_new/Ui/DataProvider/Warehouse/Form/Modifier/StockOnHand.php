<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory;
use Magestore\InventorySuccess\Model\WarehouseFactory;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Modal;
use Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Warehouse;
use Magento\Directory\Model\Config\Source\Country as SourceCountry;
use Magento\Directory\Helper\Data as DirectoryHelper;

/**
 * Class StockOnHand
 * @package Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Modifier
 */
class StockOnHand extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
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
     * @var bool
     */
    protected $_canViewStock = false;
    protected $_canViewNoneWarehouseProduct = false;
    protected $_canDeleteProduct = false;
    
    protected $_opened = false;
    protected $_groupLabel = 'Stock On-Hand';
    protected $_sortOrder = 15;
    protected $_groupContainer = 'stock_on_hand';

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
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
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
        if($permissionManagement->checkPermission(
            'Magestore_InventorySuccess::warehouse_stock_view',
            $this->getCurrentWarehouse())
        ){
            $this->_canViewStock = true;
        }
        if($permissionManagement->checkPermission(
            'Magestore_InventorySuccess::product_none_in_warehouse',
            $this->getCurrentWarehouse())
        ){
            $this->_canViewNoneWarehouseProduct = true;
        }
        if($permissionManagement->checkPermission(
            'Magestore_InventorySuccess::delete_product',
            $this->getCurrentWarehouse())
        ){
            $this->_canDeleteProduct = true;
        }
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
        if ($warehouse) {
            $warehouseData = $warehouse->getData();
            $this->loadedData[$warehouse->getId()] = $warehouseData;
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
        $warehouseId = $this->getCurrentWarehouse()->getWarehouseId();
        if ($warehouseId)
            return $this->_opened;
        return false;
    }

    /**
     * get visible
     *
     * @param
     * @return
     */
    public function getVisible()
    {
        $warehouseId = $this->getCurrentWarehouse()->getWarehouseId();
        if (!$warehouseId)
            return false;
        return $this->_visible;
    }

    /**
     * modify data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
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
        $warehouse = $this->getCurrentWarehouse();
        if(!$warehouse || !$warehouse->getWarehouseId())
            return $meta;
        
        if(!$this->_canViewStock)
            return $meta;
            
        $meta = array_replace_recursive(
            $meta,
            [
                $this->_groupContainer => [
                    'children' => $this->getChildren(),
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __($this->_groupLabel),
                                'autoRender' => true,
                                'collapsible' => true,
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
        return $meta;
    }

    /**
     * Retrieve child meta configuration
     *
     * @return array
     */
    protected function getChildren()
    {
        $children = [
            'product_buttons' => $this->getProductButtons(),
            'stock_on_hand_container' => $this->getStockContainer(),
        ];
        if($this->_canDeleteProduct){
            $children['delete_product_modal'] = $this->getDeleteProductModal();
        }
        if($this->_canViewNoneWarehouseProduct){
            $children['none_warehouse_product_modal'] = $this->getNoneWarehouseProductModal();
        }
        return $children;
    }
    
    public function getProductButtons(){
        $childrens = [];
        if($this->_canViewNoneWarehouseProduct){
            $childrens['none_warehouse_product_button'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => Container::NAME,
                            'componentType' => Container::NAME,
                            'component' => 'Magento_Ui/js/form/components/button',
                            'actions' => [
                                [
                                    'targetName' => 'os_warehouse_form.os_warehouse_form.stock_on_hand.none_warehouse_product_modal',
                                    'actionName' => 'openModal'
                                ],
                                [
                                    'targetName' => 'os_warehouse_form.os_warehouse_form.stock_on_hand.none_warehouse_product_modal.os_warehouse_product_none_in_warehouse_listing',
                                    'actionName' => 'render',
                                ],
                            ],
                            'title' => __('None Location Products'),
                            'provider' => null,
                        ],
                    ],
                ],
            ];
        }
        if($this->_canDeleteProduct) {
            $childrens['delete_product_button'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => Container::NAME,
                            'componentType' => Container::NAME,
                            'component' => 'Magento_Ui/js/form/components/button',
                            'actions' => [
                                [
                                    'targetName' => 'os_warehouse_form.os_warehouse_form.stock_on_hand.delete_product_modal',
                                    'actionName' => 'openModal'
                                ],
                                [
                                    'targetName' => 'os_warehouse_form.os_warehouse_form.stock_on_hand.delete_product_modal.os_delete_product_listing',
                                    'actionName' => 'render',
                                ],
                            ],
                            'title' => __('Delete Products'),
                            'provider' => null,
                        ],
                    ],
                ],
            ];
        }
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'label' => false,
//                        'content' => __($this->_fieldsetContent),
                        'template' => 'Magestore_InventorySuccess/form/components/button-list',
                    ],
                ],
            ],
            'children' => $childrens,
        ];
    }
    
    public function getDeleteProductModal(){
        $listingTarget = 'os_warehouse_delete_product_listing';
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'type' => 'container',
                        'options' => [
                            'onCancel' => 'actionCancel',
                            'title' => __('Delete Products'),
                        ],
                    ],
                ],
            ],
            'children' => [
                'html_content' => [
                    'arguments' => [
                        'data' => [
                            'type' => 'html_content',
                            'name' => 'html_content',
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magestore_InventorySuccess/js/form/components/html',
                                'content' => \Magento\Framework\App\ObjectManager::getInstance()
                                    ->create('Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\DeleteProduct')
                                    ->toHtml()
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
//    public function getDeleteProductModal(){
//        $listingTarget = 'os_warehouse_delete_product_listing';
//        return [
//            'arguments' => [
//                'data' => [
//                    'config' => [
//                        'componentType' => Modal::NAME,
//                        'type' => 'container',
//                        'options' => [
//                            'onCancel' => 'actionCancel',
//                            'title' => __('Delete Products'),
//                            'buttons' => [
//                                [
//                                    'text' => __('Cancel'),
//                                    'actions' => ['closeModal']
//                                ],
//                                [
//                                    'text' => __('Delete Selected Products'),
//                                    'class' => 'action-primary',
//                                    'actions' => [
//                                        [
//                                            'targetName' => 'index = ' . $listingTarget,
//                                            'actionName' => 'save',
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//            'children' => [
//                'os_delete_product_listing' => [
//                    'arguments' => [
//                        'data' => [
//                            'config' => [
//                                'component' => 'Magestore_InventorySuccess/js/warehouse/product/none-warehouse-products',
//                                'autoRender' => false,
//                                'componentType' => 'insertListing',
//                                'dataScope' => $listingTarget,
//                                'externalProvider' => $listingTarget . '.' . $listingTarget . '_data_source',
//                                'ns' => $listingTarget,
//                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
//                                'realTimeLink' => true,
//                                'dataLinks' => [
//                                    'imports' => false,
//                                    'exports' => true
//                                ],
//                                'provider' => 'os_warehouse_form.os_warehouse_form_data_source',
//                                'behaviourType' => 'simple',
//                                'externalFilterMode' => true,
//                                'imports' => [
//                                    'warehouseId' => '${ $.provider }:data.warehouse_id',
//                                ],
//                                'exports' => [
//                                    'warehouseId' => '${ $.externalProvider }:params.warehouse_id',
//                                ],
//                                'selectionsProvider' =>
//                                    $listingTarget
//                                    . '.'
//                                    . $listingTarget
//                                    . '.'
//                                    . $this->_modifierConfig['columns_ids'],
//                                'save_url' => $this->urlBuilder->getUrl('inventorysuccess/warehouse_product_delete/save'),
//                            ]
//                        ]
//                    ]
//                ]
//            ]
//        ];
//    }
    
    public function getNoneWarehouseProductModal(){
        $listingTarget = 'os_warehouse_product_none_in_warehouse_listing';
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'type' => 'container',
                        'options' => [
                            'onCancel' => 'actionCancel',
                            'title' => __('None Location Products'),
                            'buttons' => [
                                [
                                    'text' => __('Cancel'),
                                    'actions' => ['closeModal']
                                ],
                                [
                                    'text' => __($this->_modalButtonTitle),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => 'index = ' . $listingTarget,
                                            'actionName' => 'save',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                $listingTarget => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magestore_InventorySuccess/js/warehouse/product/none-warehouse-products',
                                'autoRender' => false,
                                'componentType' => 'insertListing',
                                'dataScope' => $listingTarget,
                                'externalProvider' => $listingTarget . '.' . $listingTarget . '_data_source',
                                'ns' => $listingTarget,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'dataLinks' => [
                                    'imports' => true,
                                    'exports' => true
                                ],
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true,
                                'imports' => [
                                    'warehouseId' => '${ $.provider }:data.warehouse_id',
                                ],
                                'exports' => [
                                    'warehouseId' => '${ $.externalProvider }:params.warehouse_id',
                                ],
                                'selectionsProvider' =>
                                    $listingTarget
                                    . '.'
                                    . $listingTarget
                                    . '.'
                                    . $this->_modifierConfig['columns_ids'],
                                'save_url' => $this->urlBuilder->getUrl('inventorysuccess/product_noneInWarehouse/massWarehouse'),
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    
    public function getStockContainer(){
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'sortOrder' => 10,
                        
                    ],
                ],
            ],
            'children' => [
                'html_content' => [
                    'arguments' => [
                        'data' => [
                            'type' => 'html_content',
                            'name' => 'html_content',
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/html',
                                'content' => \Magento\Framework\App\ObjectManager::getInstance()
                                    ->create('Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Stock')
                                    ->toHtml()
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $container;
    }
}