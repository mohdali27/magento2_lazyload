<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\StockActivity;


interface StockActivityProductInterface {
    
    /**
     * Get Resource Model
     * 
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getResource();
    
    /**
     * Get collection
     * 
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection();
    
}