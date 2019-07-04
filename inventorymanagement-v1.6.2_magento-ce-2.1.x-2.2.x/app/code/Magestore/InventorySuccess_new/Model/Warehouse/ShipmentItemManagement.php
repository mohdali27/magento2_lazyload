<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

use Magestore\InventorySuccess\Api\Warehouse\ShipmentItemManagementInterface;

class ShipmentItemManagement implements ShipmentItemManagementInterface
{
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory
     */
    protected $shipmentItemCollectionFactory;     
    
    /**
     * 
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory
    )
    {
        $this->shipmentItemCollectionFactory = $shipmentItemCollectionFactory;
    }

    /**
     * Get Warehouse id by shipment id
     * 
     * @param int $shipmentId
     * @return null|int
     */    
    public function getWarehouseByShipmentId($shipmentId)
    {
        $item = $this->shipmentItemCollectionFactory->create()
                        ->addFieldToFilter('parent_id', $shipmentId)
                        ->setPageSize(1)->setCurPage(1)
                        ->getFirstItem();

        return $item->getWarehouseId();
    }

}