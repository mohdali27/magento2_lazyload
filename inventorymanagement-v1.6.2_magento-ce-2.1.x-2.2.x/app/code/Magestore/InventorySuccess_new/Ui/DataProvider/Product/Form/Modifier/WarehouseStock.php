<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magestore\InventorySuccess\Ui\DataProvider\Product\Form\Modifier\StockData;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Field;

class WarehouseStock extends AbstractModifier
{

    const WAREHOUSE_STOCK_FIELDS = 'warehouse_stock';
    const WAREHOUSE_STOCK_LISTING = 'warehouse_stock_row';
    const GLOBAL_STOCK_FIELDS = 'global_stock';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface 
     */
    protected $_warehouseStockRegistry;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    
    /**
     * @var \Magento\Framework\App\RequestInterface 
     */
    private $request;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory
     */
    private $warehouseFactory;
    /**
     *
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry
     * @param \Magestore\InventorySuccess\Ui\DataProvider\Product\Form\Modifier\LocatorInterface $locator
     * @param array $modifiers
     */
    public function __construct(
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory,
        LocatorInterface $locator, 
        StockRegistryInterface $stockRegistry, 
        StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->locator = $locator;
        $this->_warehouseStockRegistry = $warehouseStockRegistry;
        $this->warehouseFactory = $collectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->locator->getProduct();
        $productId = $product->getId();
        $warehouseStocks = $this->_warehouseStockRegistry->getStockWarehouses($productId);
        $items = [];
        $globalTotalQty = 0;
        $globalQtyToShip = 0;
        if ($warehouseStocks->getSize()) {
            foreach ($warehouseStocks as $warehouseStock) {
                $items[] = $this->_formatStockData($warehouseStock->getData());
                $globalTotalQty += $warehouseStock->getTotalQty();
                $globalQtyToShip += $warehouseStock->getQtyToShip();
            }
        }
        $stockItem = $this->stockRegistry->getStockItem(
                $productId, $product->getStore()->getWebsiteId()
        );
        $data[$productId][self::DATA_SOURCE_DEFAULT][self::WAREHOUSE_STOCK_FIELDS][self::WAREHOUSE_STOCK_LISTING] = $items;
        $data[$productId][self::DATA_SOURCE_DEFAULT][self::GLOBAL_STOCK_FIELDS] = [
            'global_available_qty' => $stockItem->getQty(),
            'global_total_qty' => $globalTotalQty,
            'global_qty_to_ship' => $globalQtyToShip
                . ' ('
                . '<a target="_blank" href="'. $this->urlBuilder->getUrl('inventorysuccess/catalog/viewQtyToShip', ['id' => $productId]) . '" >'
                . ' View ' . ') </a>',
//            .'<a class="view_infor" product-name="'.$product->getName().' ('.$product->getSku().')'.'" value="'.$productId.'">'.__('View').'</a>'
//            .'<script type="text/x-magento-init">
//             function showPopupInfor(event){
//                        console.log("xxxxxxxxxx");
//                }
//                Event.observe($["a[class=view_infor]"], click, showPopupInfor);
//                .</script>',
        ];

        return $data;
    }

    /**
     * Format stock data before sending out to form
     * 
     * @param array $data
     * @return array
     */
    protected function _formatStockData($data)
    {
        $data['warehouse_id'] = $data[WarehouseProductInterface::WAREHOUSE_ID];
        $data['warehouse'] = $data['warehouse_name'] 
                            . ' ('
                            . '<a target="_blank" href="'. $this->urlBuilder->getUrl('inventorysuccess/warehouse/edit', ['id' => $data[WarehouseProductInterface::WAREHOUSE_ID]]) . '" >'
                            . $data['warehouse_code'] . ') </a>';
        $data['total_qty'] = floatval($data[WarehouseProductInterface::TOTAL_QTY]);
        $data['available_qty'] = floatval($data[WarehouseProductInterface::AVAILABLE_QTY]);
        $data['qty_to_ship'] = $data['total_qty'] - $data['available_qty'];
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $authorizationForceEdit = $objectManager->get('Magento\Backend\App\Action\Context')->getAuthorization()->isAllowed('Magestore_InventorySuccess::forcedit');
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->locator->getProduct();
        $productId = $product->getId();

        if (!$productId) {
            /* add new product */
            return $this->_modifyMetaForNewProduct($meta);
        }
        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockRegistry->getStockItem(
                $productId, $product->getStore()->getWebsiteId()
        );

        $warehouse_needs = array();
        $warehouses = $this->warehouseFactory->create();;
        $warehouseStocks = $this->_warehouseStockRegistry->getStockWarehouses($productId);
        $warehouse_exists = $warehouseStocks->getColumnValues('website_id');
        foreach($warehouses as $warehouse){
            if(!in_array($warehouse->getWarehouseId(),$warehouse_exists)){
                $array = array();
                $array['value'] = $warehouse->getWarehouseId();
                $array['label'] = $warehouse->getWarehouseName().'('.$warehouse->getWarehouseCode().')';
                array_push($warehouse_needs,$array);
            }
        }

        /* do not manage stock of product */
        if (!$this->_manageStock($stockItem)) {
            /* disable Warehouse Stock form */
            $meta = $this->_disableWarehouseStockForm($meta);
        } else {
            /* tooltip */
            $tooltip = [
                'description'=>__('Do not update catalog Qty'),
            ];
            /* remove Add button from Warehouse Stock form */
            $parentChildren = &$meta['advanced_inventory_modal']['children'];
            if($warehouseStocks->getSize() == $warehouses->getSize() ){
                $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['warehouse_stock_list_edit']['children'][self::WAREHOUSE_STOCK_LISTING] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => false,
                                'addButton' => false,
                                'addButtonLabel' => __('Add Location'),
                            ],
                        ],
                    ]
                ];
            }else{
                $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['warehouse_stock_list_edit']['children'][self::WAREHOUSE_STOCK_LISTING] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => true,
                                'addButton' => true,
                                'addButtonLabel' => __('Add Location'),
                            ],
                        ],
                    ],
                    'children' => [
                        'record' => [
                            'children' => [
                                'warehouse_select' => [
                                    'arguments' => [
                                        'data' => [
                                            'options'=> $warehouse_needs,
                                        ]
                                    ]
                                ],
                                'use_config_available_qty' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'visible' => false,
                                                'dataType' => 'number',
                                                'formElement' => Checkbox::NAME,
                                                'componentType' => Field::NAME,
                                                'description' => __(''),
                                                'dataScope' => 'use_config_available_qty',
                                                'valueMap' => [
                                                    'false' => '0',
                                                    'true' => '1',
                                                ],
                                                'value' => '0',
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ]
                    ]
                ];
            }

            $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['warehouse_stock_list']['children'][self::WAREHOUSE_STOCK_LISTING] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'addButton' => false,
                            'addButtonLabel' => __('Add Location'),
                        ],
                    ],
                ],
                'children' => [
                    'record' => [
                        'children' => [
                            'warehouse_select' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'visible' => false,
                                        ]
                                    ]
                                ]
                            ],
                            'action_delete' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'visible' => false,
                                        ]
                                    ]
                                ]
                            ],
                            'available_qty'=>[
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'additionalClasses' => 'admin__field',
                                            'dataScope' => 'available_qty',
                                            'imports' => [
                                                'disabled' =>
                                                    '!${$.parentName}.use_config_available_qty'
                                                    . ':checked',
                                            ],
                                        ]
                                    ]
                                ]
                            ],
                            'use_config_available_qty' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'additionalClasses' => 'admin__field-x-small admin__field',
                                            //'tooltip' => $tooltip,
                                            'dataType' => 'number',
                                            'formElement' => Checkbox::NAME,
                                            'componentType' => Field::NAME,
                                            //'description' => __('Force edit'),
                                            'dataScope' => 'use_config_available_qty',
                                            'valueMap' => [
                                                'false' => '0',
                                                'true' => '1',
                                            ],
                                            'value' => '0',
                                            'disabled' => !$authorizationForceEdit,
                                        ],
                                    ],
                                ],
                            ],
                            'total_qty' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'additionalClasses' => 'admin__field-x-small admin__field',
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ]
                ]
            ];
        }


        if($product->isComposite()) {
            $meta = $this->_disableFields($meta,$warehouse_needs);
        }

        return $meta;
    }

    /**
     * Modify meta for New Product form
     * 
     * @param array $meta
     * @return array
     */
    private function _modifyMetaForNewProduct($meta)
    {

        if($this->_isNotManageStock()) {
            /* do not manage stock in warehouse of these products */
            /* disable warehouse stock form */
            $meta = $this->_disableWarehouseStockForm($meta);
            return $meta;
        }
        $parentChildren = &$meta['advanced_inventory_modal']['children'];
        $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['global_stock'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => false,
                    ],
                ],
            ],
            'children' => [
                'global_available_qty' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => false,
                            ]
                        ]
                    ]
                ],
                'global_qty_to_ship' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => false,
                            ]
                        ]
                    ]
                ],
                'global_total_qty' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => false,
                            ]
                        ]
                    ]
                ]                
            ]          
        ];

        $visible = true;
        $type = $this->request->getParam('type');
        if(in_array($type, [
            \Magento\Bundle\Model\Product\Type::TYPE_CODE,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
        ])) {
            $visible = false;
        }

        $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['warehouse_stock_list']['children'][self::WAREHOUSE_STOCK_LISTING] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButton' => true,
                        'addButtonLabel' => __('Add Location'),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'children' => [
                        'warehouse' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => false,
                                    ]
                                ]
                            ]
                        ],
                        'available_qty'=>[
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => false,
                                        'additionalClasses' => 'admin__field-x-small',
                                        'dataScope' => 'available_qty',
                                        'imports' => [
                                            'disabled' =>
                                                '${$.parentName}.use_config_available_qty'
                                                . ':checked',
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        'use_config_available_qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => false,
                                        'dataType' => 'number',
                                        'formElement' => Checkbox::NAME,
                                        'componentType' => Field::NAME,
                                        'description' => __(''),
                                        'dataScope' => 'use_config_available_qty',
                                        'valueMap' => [
                                            'false' => '0',
                                            'true' => '1',
                                        ],
                                        'value' => '0',
                                    ],
                                ],
                            ],
                        ],
                        'qty_to_ship' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => false,
                                    ]
                                ]
                            ]
                        ],

                        'total_qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                        'imports' => [
                                            'visible' => $visible ? 1 : 0,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'shelf_location' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                    ]
                                ]
                            ]
                        ],

                    ]
                ]
            ]
        ];

        $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['warehouse_stock_list_edit']['children'][self::WAREHOUSE_STOCK_LISTING] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => false,
                        'addButton' => false,
                        'addButtonLabel' => __('Add Location'),
                    ],
                ],
            ]
        ];

        return $meta;
    }
    
    /**
     * Disable warehouser stock form
     * 
     * @param array $meta
     * @return array
     */
    private function _disableWarehouseStockForm(array $meta)
    {
        $parentChildren = &$meta['advanced_inventory_modal']['children'];
        $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['arguments']['data']['config']['visible'] = false;
        /* open Stock Data form */
        if (!empty($parentChildren[StockData::STOCK_DATA_FIELDS])) {
            $parentChildren[StockData::STOCK_DATA_FIELDS] = array_replace_recursive(
                    $parentChildren[StockData::STOCK_DATA_FIELDS], [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'collapsible' => false,
                            'opened' => true,
                            ],
                        ],
                    ],
                ]
            );
        }     
        return $meta;
    }

    /**
     * Check manage stock
     * 
     * @param StockItemInterface $stockItem
     * @return bool
     */
    private function _manageStock(StockItemInterface $stockItem)
    {
        if (!$stockItem->getItemId() || $stockItem->getUseConfigManageStock()) {
            return $this->stockConfiguration->getDefaultConfigValue(StockItemInterface::MANAGE_STOCK);
        }
        return $stockItem->getManageStock();
    }
    
    /**
     * 
     * @return boolean
     */
    private function _isNotManageStock()
    {
        $type = $this->request->getParam('type');
        
        if(in_array($type, [
           //\Magento\Bundle\Model\Product\Type::TYPE_CODE,
           //\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
           //\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
           //\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
           \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
        ])) {
            return true;
        }
        return false;

    }

    /**
     * Modify meta for Product form
     *
     * @param array $meta
     * @param array $warehouse_needs
     * @return array
     */
    private function _disableFields($meta , $warehouse_needs){
        $visible = false;
        $parentChildren = &$meta['advanced_inventory_modal']['children'];
        $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['global_stock'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => $visible,
                    ],
                ],
            ],
            'children' => [
                'global_available_qty' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => $visible,
                            ]
                        ]
                    ]
                ],
                'global_qty_to_ship' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => $visible,
                            ]
                        ]
                    ]
                ],
                'global_total_qty' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => $visible,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['warehouse_stock_list_edit']['children'][self::WAREHOUSE_STOCK_LISTING] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => true,
                        'addButton' => true,
                        'addButtonLabel' => __('Add Warehouse'),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'children' => [
                        'warehouse_select' => [
                            'arguments' => [
                                'data' => [
                                    'options'=> $warehouse_needs,
                                ]
                            ]
                        ],
                        'use_config_available_qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                    ],
                                ],
                            ],
                        ],
                        'total_qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                        'imports' => [
                                            'visible' => 0,
                                        ]
                                    ],
                                ],
                            ],
                        ],
                        'shelf_location' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        ];

        if(count($warehouse_needs) < 1){
            $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['warehouse_stock_list_edit']['children'][self::WAREHOUSE_STOCK_LISTING] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'visible' => false,
                            'addButton' => false,
                            'addButtonLabel' => __('Add Warehouse'),
                        ],
                    ],
                ]
            ];
        }

        $parentChildren[self::WAREHOUSE_STOCK_FIELDS]['children']['warehouse_stock_list']['children'][self::WAREHOUSE_STOCK_LISTING] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButton' => false,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'children' => [
                        'warehouse_select' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                    ],
                                ],
                            ],
                        ],
                        'available_qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                    ],
                                ],
                            ],
                        ],
                        'use_config_available_qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                    ],
                                ],
                            ],
                        ],
                        'qty_to_ship' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                    ],
                                ],
                            ],
                        ],
                        'total_qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                        'imports' => [
                                            'visible' => 0,
                                        ]
                                    ],
                                ],
                            ],
                        ],
                        'shelf_location' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $visible,
                                    ],
                                ],
                            ],
                        ],
                        'action_delete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => true,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        ];

        return $meta;

    }

}
