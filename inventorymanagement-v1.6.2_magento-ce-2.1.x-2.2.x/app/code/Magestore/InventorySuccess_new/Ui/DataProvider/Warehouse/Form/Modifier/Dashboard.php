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
class Dashboard extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
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

    protected $_opened = true;
    protected $_groupLabel = 'Dashboard';
    protected $_sortOrder = 50;
    protected $_groupContainer = 'dashboard';

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
        if(!$warehouse || !$warehouse->getWarehouseId()){
            return $meta;
        }
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
            'dashboard_container' => $this->getDashboardContainer(),
        ];
        return $children;
    }
    
    public function getDashboardContainer(){
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
                                    ->create('Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Dashboard')
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