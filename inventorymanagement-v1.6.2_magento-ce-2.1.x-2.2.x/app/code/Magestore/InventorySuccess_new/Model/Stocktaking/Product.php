<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Stocktaking;

use \Magento\Framework\Model\AbstractModel;
use \Magestore\InventorySuccess\Api\StockActivity\StockActivityProductInterface;

/**
 * Class Product
 * @package Magestore\InventorySuccess\Model\Stocktaking
 */
class Product extends AbstractModel implements StockActivityProductInterface
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct(){
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Product');
    }
}
