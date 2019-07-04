<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Permission\Warehouse\Modifier;


use Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic;
use Magento\Ui\Component\Form;

/**
 * Class ModifierFormDataProvider
 * @package Magestore\InventorySuccess\Ui\DataProvider\Permission\Warehouse\Modifier
 */
class ModifierFormDataProvider extends Dynamic
{
    /**
     * Group Container
     *
     * @var string
     */
    protected $_groupContainer = 'warehouse_permission';

    /**
     * Group Template
     *
     * @var string
     */
    protected $_groupLabel = '';

    /**
     * Fieldset Content
     *
     * @var string
     */
    protected $_fieldsetContent = '';

    /**
     * Group Container
     *
     * @var string
     */
    protected $_opened = false;

    /**
     * Button Title
     *
     * @var string
     */
    protected $_buttonTitle = 'Assign Staffs';

    /**
     * Modal Title
     *
     * @var string
     */
    protected $_modalTitle = '';


    /**
     * Modal Button Title
     *
     * @var string
     */
    protected $_modalButtonTitle = 'Add Selected Staff';

    /**
     * Modifier Config
     *
     * @var array
     */
    protected $_modifierConfig = [
        'button_set' => 'warehouse_permission_button_set',
        'modal' => 'warehouse_permission_modal',
        'listing' => 'os_warehouse_permission_user_listing',
        'columns_ids' => 'os_warehouse_permission_user_listing_columns.ids',
        'form' => 'os_warehouse_permission_form'
    ];

    /**
     * Fields Map
     *
     * @var array
     */
    protected $_mapFields = [
        'id' => 'user_id',
        'user_id' => 'user_id',
        'username' => 'username',
        'role_id' => 'role_id'
    ];

    /**
     * @var \Magento\Authorization\Model\ResourceModel\Role\Grid\Collection
     */
    protected $_roleCollection;

    /**
     * ModifierFormDataProvider constructor.
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Authorization\Model\ResourceModel\Role\Grid\Collection $roleCollection
     * @param array $modifierConfig
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Authorization\Model\ResourceModel\Role\Grid\Collection $roleCollection,
        array $modifierConfig = []
    ){
        parent::__construct( $urlBuilder, $request, $modifierConfig);
        $this->_roleCollection = $roleCollection;
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
                    'children' => $this->getModifierChildren(),
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __($this->_groupLabel),
                                'collapsible' => false,
                                'visible' => $this->getVisible(),
                                'opened' => $this->getOpened(),
                                'componentType' => 'container',
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
     * Fill meta columns
     *
     * @return array
     */
    protected function fillModifierMeta()
    {
        return [
            'id' => $this->getTextColumn('id', false, __('User ID'), 10),
            'username' => $this->getTextColumn('username', false, __('Staff'), 20),
            'role_id' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'formElement' => Form\Element\Select::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => 'role_id',
                            'sortOrder' => 40,
                            'label' => __('Location Roles'),
                            'options' => $this->_roleCollection->toOptionArray()
                        ],
                    ],
                ],
            ],
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 90,
                            'fit' => true,
                        ],
                    ],
                ],
            ],
            'position' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => 'position',
                            'sortOrder' => 100,
                            'visible' => false,
                        ],
                    ],
                ],
            ],
        ];
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
                        'content' => __($this->_fieldsetContent),
                        'template' => 'Magestore_InventorySuccess/form/components/button-list',
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
                                        'targetName' =>
                                            $this->_modifierConfig['form'] . '.' . $this->_modifierConfig['form'],
                                        'actionName' => 'save',
                                    ]
                                ],
                                'title' => __('Save Staff Permissions'),
                                'provider' => null,
                            ],
                        ],
                    ],
                ],
                'grouped_products_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' =>
                                            $this->_modifierConfig['form'] . '.' . $this->_modifierConfig['form']
                                            . '.'
                                            . $this->_groupContainer
                                            . '.'
                                            . $this->_modifierConfig['modal'],
                                        'actionName' => 'openModal',
                                    ],
                                    [
                                        'targetName' =>
                                            $this->_modifierConfig['form'] . '.' . $this->_modifierConfig['form']
                                            . '.'
                                            . $this->_groupContainer
                                            . '.'
                                            . $this->_modifierConfig['modal']
                                            . '.'
                                            . $this->_modifierConfig['listing'],
                                        'actionName' => 'render',
                                    ],
                                ],
                                'title' => __($this->_buttonTitle),
                                'provider' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
