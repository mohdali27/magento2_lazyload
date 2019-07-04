<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created by PhpStorm.
 * User: zero
 * Date: 06/04/2016
 * Time: 09:29
 */

namespace Magestore\InventorySuccess\Model\LowStockNotification;

class Rule extends \Magento\Rule\Model\AbstractModel
    implements \Magestore\InventorySuccess\Api\Data\LowStockNotification\RuleInterface
{

    /**#@+
     * Rule's statuses
     */
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * Rule's update time type
     */
    const TIME_TYPE_DAILY = 1;
    const TIME_TYPE_MONTHLY = 2;

    /**
     * Rule's update type
     */
    const TYPE_BOTH_SYSTEM_AND_WAREHOUSE = 1;
    const TYPE_ONLY_SYSTEM = 2;
    const TYPE_ONLY_WAREHOUSE = 3;

    /**
     * Rule's apply type
     */
    const  APPLIED = 1;
    const NOT_APPLY = 0;

    /**
     * Rule's update time type
     */
    const TYPE_LOWSTOCK_THRESHOLD_SALE_DAY = 1;
    const TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY = 2;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $_combineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $_actionCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $_warehouseCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollection;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var RuleManagement
     */
    protected $_ruleManagement;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magestore\InventorySuccess\Model\LowStockNotification\RuleManagement $ruleManagement,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_productCollection = $productCollection;
        $this->_productFactory = $productFactory;
        $this->_ruleManagement = $ruleManagement;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule');
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    /**
     * Prepare data before saving
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeSave()
    {
        $nextTime = $this->_ruleManagement->getNewNextTime($this);
        $this->setNextTimeAction($nextTime);
        parent::beforeSave();
        return $this;
    }

    /**
     * Prepare data before saving
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterSave()
    {
        parent::afterSave();
        return $this;
    }

    /**
     * Prepare rule's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_INACTIVE => __('Inactive')
        ];
    }

    /**
     * Prepare rule's applies.
     *
     * @return array
     */
    public function getAvailableApply()
    {
        return [
            self::APPLIED => __('Yes'),
            self::NOT_APPLY => __('No')
        ];
    }

    /**
     * Prepare rule's update time type.
     *
     * @return array
     */
    public function getAvailableUpdateTimeType()
    {
        return [
            self::TIME_TYPE_DAILY => __('Daily'),
            self::TIME_TYPE_MONTHLY => __('Monthly')
        ];
    }

    /**
     * Prepare rule's update type.
     *
     * @return array
     */
    public function getAvailableUpdateType()
    {
        return [
            self::TYPE_BOTH_SYSTEM_AND_WAREHOUSE => __('Both of Location and Global'),
            self::TYPE_ONLY_SYSTEM => __('Global'),
            self::TYPE_ONLY_WAREHOUSE => __('Location')
        ];
    }

    /**
     * Prepare rule's Low Stock Threshold type.
     *
     * @return array
     */
    public function getAvailableLowStockThresholdType()
    {
        return [
            self::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY => __('Availability Days'),
            self::TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY => __('Availability Qty')
        ];
    }

    /**
     * Prepare rule's warehouses.
     *
     * @return array
     */
    public function getAvailableWarehouse()
    {
        $warehouseCollection = $this->_warehouseCollectionFactory->create();
        $warehouses = [];
        if (!empty($warehouseCollection)) {
            foreach ($warehouseCollection as $warehouse) {
                $warehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
            }
        }
        return $warehouses;
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }
}