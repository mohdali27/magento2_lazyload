<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Request\Form\Modifier;

use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Model\TransferStock;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockSummary extends \Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Request\Form\Modifier\RequestStock
{

    protected $_sortOrder = '1';
    protected $_groupContainer = 'stock_summary';

    protected $_dataLinks = 'stock_summary';

    protected $_groupLabel = 'Stock Summary';


    protected $_fieldsetContent = 'Stock summary for this request';


    protected $_buttonTitle = 'Add Products to Deliver';


    protected $_modalTitle = 'Add Products to Deliver';


    protected $_modalButtonTitle = 'Add Selected Products';

    protected $_modifierConfig = [
        'button_set' => 'product_stock_button_set',
        'modal' => 'product_stock_modal',
        'listing' => 'transferstock_warehouse_product_stock_listing',
        'form' => 'transferstock_request_form',
        'history_listing' => 'transferstock_request_stock_summary',
        'columns_ids' => 'id'
    ];

    protected $_mapFields = [
        'id' => 'entity_id',
        'sku' => 'sku',
        'name' => 'name',
        'qty' => 'qty',
        'request_qty' => 'request_qty'
    ];


    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        if (!$this->getVisible()) {
            return $data;
        }
        return parent::modifyData($data);


    }

    public function getVisible()
    {
        $transferStock = $this->getCurrentTransferStock();
        if ($transferStock->getStatus() == TransferStock::STATUS_PROCESSING ||
            $transferStock->getStatus() == TransferStock::STATUS_COMPLETED
        ) {
            return true;
        }
        return false;
    }


    /**
     * Retrieve child meta configuration
     *
     * @return array
     */
    protected function getModifierChildren()
    {

        $children = [
            $this->_modifierConfig['button_set'] => $this->getCustomButtons(),
            //$this->_modifierConfig['modal'] => $this->getCustomModal(),
            $this->_modifierConfig['history_listing'] => $this->getStockSummary(),
        ];

        return $children;
    }

    protected function canEditProductList()
    {
        $transferStock = $this->getCurrentTransferStock();
        if ($transferStock->getStatus() == TransferStock::STATUS_COMPLETED) {
            return false;
        }

        if (!$this->_permissionManagement->checkPermission(TransferPermission::REQUEST_STOCK_EDIT_PRODUCT)) {
            return false;
        }

        return true;
    }

    protected function getStockSummary()
    {
        $render_url = 'mui/index/render';
        $grid = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'autoRender' => true,
                        'componentType' => 'insertListing',
                        'dataScope' => $this->_modifierConfig['history_listing'],
                        'externalProvider' =>
                            $this->_modifierConfig['history_listing']
                            . '.'
                            . $this->_modifierConfig['history_listing']
                            . '_data_source',
                        'ns' => $this->_modifierConfig['history_listing'],
                        'params' => ['id' => $this->request->getParam('id')],
                        'render_url' => $this->urlBuilder->getUrl($render_url),
                        'realTimeLink' => true,
                        'dataLinks' => [
                            'imports' => false,
                            'exports' => true
                        ],
                        'behaviourType' => 'simple',
                        'externalFilterMode' => true,
                        'imports' => [
                            'transferstock_id' => '${ $.provider }:data.transferstock_id',
                        ],
                        'exports' => [
                            'transferstock_id' => '${ $.externalProvider }:params.transferstock_id',
                        ],
                    ],
                ],
            ],
        ];
        return $grid;
    }


    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->getVisible()) {
            return $meta;
        }

        $meta = array_replace_recursive(
            $meta,
            [
                $this->_groupContainer => [
                    'children' => $this->getModifierChildren(),
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __($this->_groupLabel),
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
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getCustomButtons()
    {

        $buttons = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => __($this->_fieldsetContent),
                        'template' => 'Magestore_InventorySuccess/form/components/button-list',
                    ],
                ],
            ],
        ];
        if ($this->canEditProductList()) {
            $buttons = $this->getEditProductListButton($buttons);
        }

        $buttons = $this->getDownloadSummaryButton($buttons);

        return $buttons;
    }

    public function getEditProductListButton($buttons)
    {
        $buttons['children']['edit_product_list_button'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/button',
                        'actions' => [
                            [
                                'targetName' =>
                                    $this->_modifierConfig['form'] . '.' . $this->_modifierConfig['form'],

                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'id' => $this->request->getParam('id'),
                                        'status' => \Magestore\InventorySuccess\Model\TransferStock::STATUS_PENDING,
                                        'action' => 'edit_product'
                                    ],
                                ]
                            ]
                        ],
                        'title' => __('Edit Product'),
                        'provider' => null,
                    ],
                ],
            ],
        ];

        return $buttons;
    }


    public function getDownloadSummaryButton($buttons)
    {

        $buttons['children']['download_summary_button'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magestore_InventorySuccess/js/form/components/export-button',
                        'actions' => [
                            [
                                'targetName' =>
                                    $this->_modifierConfig['form'] . '.' . $this->_modifierConfig['form'],

                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'id' => $this->request->getParam('id'),
                                        'action' => 'download_summary',
                                    ],
                                ]
                            ]
                        ],
                        'title' => __('Download Summary'),
                        'provider' => null,
                    ],
                ],
            ],
        ];
        return $buttons;
    }
}
