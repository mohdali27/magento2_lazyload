<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\DynamicRows;


/**
 * Class AbstractModifier
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Dynamic extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\AbstractModifier
    implements ModifierInterface
{

    /**
     * Group Container
     *
     * @var string
     */
    protected $_groupContainer = 'os_adjuststock';

    /**
     * Group Label
     *
     * @var string
     */
    protected $_groupLabel = 'Product List';

    /**
     * sort Order
     *
     * @var string
     */
    protected $_sortOrder = '10';

    /**
     * Fieldset Content
     *
     * @var string
     */
    protected $_fieldsetContent = '';

    /**
     * Data Links Type
     *
     * @var string
     */
    protected $_dataLinks = 'associated';

    /**
     * Use button set
     *
     * @var string
     */
    protected $_useButtonSet = true;

    /**
     * Button Title
     *
     * @var string
     */
    protected $_buttonTitle = 'Add Products';

    /**
     * Modal Title
     *
     * @var string
     */
    protected $_modalTitle = 'Add Products';

    /**
     * Modal data id
     *
     * @var string
     */
    protected $_modalDataId = 'adjuststock_id';

    /**
     * Modal data column
     *
     * @var string
     */
    protected $_modalDataColumn = 'warehouse_id';

    /**
     * Modal Button Title
     *
     * @var string
     */
    protected $_modalButtonTitle = 'Add Selected Products';

    /**
     * Container Prefix
     *
     * @var string
     */
    protected $_containerPrefix = 'container_';

    /**
     * Container Prefix
     *
     * @var string
     */
    protected $_importTitle = 'Import products';

    /**
     * @var array
     */
    protected $_modalListingRenderParams = [];

    /**
     * @var string
     */
    protected $_warehouse_label_id = 'warehouse_label_id';

    /**
     * Modifier Config
     *
     * @var array
     */
    protected $_modifierConfig = [
        'button_set' => 'product_stock_button_set',
        'modal' => 'product_stock_modal',
        'listing' => 'os_adjuststock_product_listing',
        'form' => 'os_adjuststock_form',
        'columns_ids' => 'product_columns.ids',
    ];

    /**
     * Fields Map
     *
     * @var array
     */
    protected $_mapFields = [
        'id' => 'entity_id',
        'sku' => 'sku',
        'name' => 'name',
    ];

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * get fieldset content
     *
     * @param
     * @return
     */
    public function getFieldsetContent(){
        return $this->_fieldsetContent;
    }

    /**
     * set fieldset content
     *
     * @param string
     * @return
     */
    public function setFieldsetContent($fieldsetContent){
        $this->_fieldsetContent = $fieldsetContent;
    }

    /**
     * get use button set
     *
     * @param
     * @return
     */
    public function getUseButtonSet(){
        return $this->_useButtonSet;
    }

    /**
     * set use button set
     *
     * @param string
     * @return
     */
    public function setUseButtonSet($useButtonSet){
        $this->_useButtonSet = $useButtonSet;
    }

    /**
     * get use button title
     *
     * @param
     * @return
     */
    public function getButtonTitle(){
        return $this->_buttonTitle;
    }

    /**
     * set use button title
     *
     * @param string
     * @return
     */
    public function setButtonTitle($getButtonTitle){
        $this->_buttonTitle = $getButtonTitle;
    }

    /**
     * get use modal title
     *
     * @param
     * @return
     */
    public function getModalTitle(){
        return $this->_modalTitle;
    }

    /**
     * set use modal title
     *
     * @param string
     * @return
     */
    public function setModalTitle($modalTitle){
        $this->_modalTitle = $modalTitle;
    }

    /**
     * get use import title
     *
     * @param
     * @return
     */
    public function getImportTitle(){
        return $this->_importTitle;
    }

    /**
     * set use import title
     *
     * @param string
     * @return
     */
    public function setImportTitle($importTitle){
        $this->_importTitle = $importTitle;
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
                                'label' => __($this->getGroupLabel()),
                                'collapsible' => $this->getCollapsible(),
                                'visible' => $this->getVisible(),
                                'opened' => $this->getOpened(),
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => $this->getSortOrder()
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
    protected function getModifierChildren()
    {
        $children = [
            $this->_modifierConfig['button_set'] => $this->getCustomButtons(),
            $this->_modifierConfig['modal'] => $this->getCustomModal(),
            $this->_dataLinks => $this->getDynamicGrid(),
        ];
        return $children;
    }

    /**
     * Returns Modal configuration
     *
     * @return array
     */
    protected function getCustomModal()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope' => '',
                        'provider' =>
                            $this->_modifierConfig['form']
                            . '.'
                            . $this->_modifierConfig['form']
                            . '_data_source',
                        'options' => [
                            'title' => __($this->getModalTitle()),
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
                                            'targetName' => 'index = ' . $this->_modifierConfig['listing'],
                                            'actionName' => 'save'
                                        ],
                                        'closeModal'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [$this->_modifierConfig['listing'] => $this->getModalListing()],
        ];
    }

    /**
     * Returns Listing configuration
     *
     * @return array
     */
    protected function getModalListing()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'autoRender' => false,
                        'componentType' => 'insertListing',
                        'dataScope' => $this->_modifierConfig['listing'],
                        'externalProvider' =>
                            $this->_modifierConfig['listing']
                            . '.'
                            . $this->_modifierConfig['listing']
                            . '_data_source',
                        'selectionsProvider' =>
                            $this->_modifierConfig['listing']
                            . '.'
                            . $this->_modifierConfig['listing']
                            . '.'
                            . $this->_modifierConfig['columns_ids'],
                        'ns' => $this->_modifierConfig['listing'],
                        'render_url' => $this->urlBuilder
                            ->getUrl('mui/index/render', $this->_modalListingRenderParams),
                        'realTimeLink' => true,
                        'provider' =>
                            $this->_modifierConfig['form']
                            . '.'
                            . $this->_modifierConfig['form']
                            . '_data_source',
                        'dataLinks' => ['imports' => false, 'exports' => true],
                        'behaviourType' => 'simple',
                        'externalFilterMode' => true,
                        'imports' => [
                            $this->_modalDataId => '${ $.provider }:data.'.$this->_modalDataId,
                            $this->_modalDataColumn => '${ $.provider }:data.'.$this->_modalDataColumn,
                        ],
                        'exports' => [
                            $this->_modalDataId => '${ $.externalProvider }:params.'.$this->_modalDataId,
                            $this->_modalDataColumn => '${ $.externalProvider }:params.'.$this->_modalDataColumn,
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
        if(!$this->getUseButtonSet())
            return [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'label' => false,
                            'template' => 'Magestore_InventorySuccess/form/components/button-list',
                        ],
                    ],
                ]
            ];
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => __($this->getFieldsetContent()),
                        'template' => 'Magestore_InventorySuccess/form/components/button-list',
                    ],
                ],
            ],
            'children' => array_replace_recursive([
                'dynamic_button' => [
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
                                'title' => __($this->getButtonTitle()),
                                'provider' => null,
                            ],
                        ],
                    ],
                ],
            ],
                $this->getAdditionalButtons()
            )
        ];
    }

    /**
     * get additional buttons
     *
     * @return array
     */
    protected function getAdditionalButtons()
    {
        return [];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getDynamicGrid()
    {
        $grid = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'label' => null,
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magestore_InventorySuccess/js/dynamic-rows/dynamic-rows-grid',
                        'addButton' => false,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data.links',
                        'deleteButtonLabel' => __('Remove'),
                        'dataProvider' => $this->_modifierConfig['listing'],
                        'map' => $this->_mapFields,
                        'links' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                        'sortOrder' => 20,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                    ],
                ],
            ],
            'children' => $this->getRows(),
        ];
        return $grid;
    }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return array
     */
    protected function getRows()
    {
        return [
            'record' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'container',
                            'isTemplate' => true,
                            'is_collection' => true,
                            'component' => 'Magento_Ui/js/dynamic-rows/record',
                            'dataScope' => '',
                        ],
                    ],
                ],
                'children' => $this->fillModifierMeta(),
            ],
        ];
    }

    /**
     * Fill meta columns
     *
     * @return array
     */
    protected function fillModifierMeta()
    {
        return [
            'id' => $this->getTextColumn('id', true, __('ID'), 10),
            'sku' => $this->getTextColumn('sku', false, __('SKU'), 20),
            'name' => $this->getTextColumn('name', false, __('Name'), 30),
        ];
    }

    /**
     * Returns text column configuration for the dynamic grid
     *
     * @param string $dataScope
     * @param bool $fit
     * @param Phrase $label
     * @param int $sortOrder
     * @return array
     */
    protected function getTextColumn($dataScope, $fit, Phrase $label, $sortOrder)
    {
        $column = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'dataScope' => $dataScope,
                        'fit' => $fit,
                        'label' => $label,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
        return $column;
    }
}
