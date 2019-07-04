<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory;
use Magestore\InventorySuccess\Model\WarehouseFactory;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Warehouse;
use Magento\Directory\Helper\Data as DirectoryHelper;

/**
 * Class StockMovement
 * @package Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Modifier
 */
class StockMovement extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
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
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_currentProduct;

    protected $_groupLabel = 'Stock Movement';
    protected $_sortOrder = 150;
    protected $_groupContainer = 'stock_movement';

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
        \Magento\Framework\Registry $registry,
        DirectoryHelper $directoryHelper,
        array $_modifierConfig = []
    )
    {
        parent::__construct($urlBuilder, $request, $_modifierConfig);
        $this->collection = $collectionFactory->create();
        $this->_warehouseFactory = $warehouseFactory;
        $this->_coreRegistry = $registry;
        $this->directoryHelper = $directoryHelper;
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
     * @return \Magento\Catalog\Model\Product
     * @throws NoSuchEntityException
     */
    public function getCurrentProduct()
    {
        if (!$this->_currentProduct)
            $this->_currentProduct = $this->_coreRegistry->registry('current_product');
        return $this->_currentProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->getCurrentProduct() || !$this->getCurrentProduct()->getId())
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
                                'visible' => true,
                                'opened' => false,
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
            'list_stock_movement' => $this->getStockMovements(),
        ];
        return $children;
    }

    protected function getStockMovements()
    {
        $listingTarget = 'os_product_stockmovement_listing';
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'autoRender' => true,
                        'componentType' => 'insertListing',
                        'dataScope' => $listingTarget,
                        'externalProvider' => $listingTarget . '.' . $listingTarget . '_data_source',
                        'ns' => $listingTarget,
                        'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                        'realTimeLink' => true,
                        'dataLinks' => [
                            'imports' => false,
                            'exports' => true
                        ],
                        'behaviourType' => 'simple',
                        'externalFilterMode' => true,
                        'imports' => [
                            'product_id' => '${ $.provider }:data.product.current_product_id',
                        ],
                        'exports' => [
                            'product_id' => '${ $.externalProvider }:params.product_id',
                        ]
                    ],
                ],
            ],
        ];
    }
}