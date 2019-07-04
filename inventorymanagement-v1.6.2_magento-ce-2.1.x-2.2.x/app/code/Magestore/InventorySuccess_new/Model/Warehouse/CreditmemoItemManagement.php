<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

use Magestore\InventorySuccess\Api\Warehouse\CreditmemoItemManagementInterface;

class CreditmemoItemManagement implements CreditmemoItemManagementInterface
{

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory
     */
    protected $creditmemoItemCollectionFactory; 
    
    /**
     * 
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory $creditmemoItemCollectionFactory
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory $creditmemoItemCollectionFactory
    )
    {
        $this->creditmemoItemCollectionFactory = $creditmemoItemCollectionFactory;
    }

    /**
     * Get Warehouse id by creditmemo item id
     * 
     * @param int $itemId
     * @return null|int
     */    
    public function getWarehouseByCreditmemoItemId($itemId)
    {
        $item = $this->creditmemoItemCollectionFactory->create()
                        ->addFieldToFilter('entity_id', $itemId)
                        ->setPageSize(1)->setCurPage(1)
                        ->getFirstItem();

        return $item->getWarehouseId();
    }

}