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

namespace Magestore\InventorySuccess\Model\SupplyNeeds;

class SupplyNeedsManagement extends \Magento\Framework\Model\AbstractModel
    implements \Magestore\InventorySuccess\Api\SupplyNeeds\SupplyNeedsManagementInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\SupplyNeeds
     */
    protected $_supplyNeedsResourceModel;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\ResourceModel\SupplyNeeds $supplyNeedsResourceModel,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_supplyNeedsResourceModel = $supplyNeedsResourceModel;
    }

    /**
     * @param $topFilter
     * @return \Magestore\InventorySuccess\Model\ResourceModel\SupplyNeeds\Product\Collection
     */
    public function getProductSupplyNeedsCollection($topFilter, $sort, $dir)
    {
        $collection = $this->_supplyNeedsResourceModel->getProductSupplyNeedsCollection($topFilter, $sort, $dir);
        return $collection;
    }

    /**
     * @param $salesPeriod
     * @return bool|string
     */
    public function getFromDateBySalePeriod($salesPeriod) {
        if ($salesPeriod == \Magestore\InventorySuccess\Model\SupplyNeeds::SALES_PERIOD_LAST_7_DAYS) {
            $fromDate = date('Y-m-d', strtotime('-7 days', strtotime(date('Y-m-d'))));
        }
        if ($salesPeriod == \Magestore\InventorySuccess\Model\SupplyNeeds::SALES_PERIOD_LAST_30_DAYS) {
            $fromDate = date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d'))));
        }
        if ($salesPeriod == \Magestore\InventorySuccess\Model\SupplyNeeds::SALES_PERIOD_3_MONTHS) {
            $fromDate = date('Y-m-d', strtotime('-3 months', strtotime(date('Y-m-d'))));
        }
        return $fromDate;
    }

    /**
     * @param $topFilter
     * @return mixed
     */
    public function getMoreInformationToExport($topFilter)
    {
        return $this->_supplyNeedsResourceModel->getMoreInformationToExport($topFilter);
    }



}