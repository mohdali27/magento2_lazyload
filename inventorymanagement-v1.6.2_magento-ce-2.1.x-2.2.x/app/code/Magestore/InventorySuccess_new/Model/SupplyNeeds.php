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

namespace Magestore\InventorySuccess\Model;

class SupplyNeeds extends \Magento\Framework\Model\AbstractModel
    implements \Magestore\InventorySuccess\Api\Data\SupplyNeeds\SupplyNeedsInterface
{
    const SALES_PERIOD_LAST_7_DAYS = 'last_7_days';
    const SALES_PERIOD_LAST_30_DAYS = 'last_30_days';
    const SALES_PERIOD_3_MONTHS = 'last_3_months';
    const CUSTOM_RANGE = 'custom';

    /**
     * @var ResourceModel\Warehouse\Collection
     */
    protected $_warehouseCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection $warehouseCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    /**
     * Prepare warehouses
     *
     * @return array
     */
    public function getWarehouses()
    {
        $warehouseCollection = $this->_warehouseCollectionFactory;
        $warehouseArray = [];
        if (!empty($warehouseCollection)) {
            foreach ($warehouseCollection as $warehouse) {
                $warehouseArray[$warehouse->getId()] = $warehouse->getWarehouseName();
            }
        }
        return $warehouseArray;
    }

    /**
     * @return array
     */
    public function getSalesPeriod()
    {
        return [
            self::SALES_PERIOD_LAST_7_DAYS => __('Last 7 days'),
            self::SALES_PERIOD_LAST_30_DAYS => __('Last 30 days'),
            self::SALES_PERIOD_3_MONTHS => __('Last 3 months'),
            self::CUSTOM_RANGE => __('Custom Range')
        ];
    }
}