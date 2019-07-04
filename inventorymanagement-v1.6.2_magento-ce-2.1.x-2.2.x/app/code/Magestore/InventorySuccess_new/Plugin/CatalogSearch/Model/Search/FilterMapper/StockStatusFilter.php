<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\CatalogSearch\Model\Search\FilterMapper;

if (!class_exists('Magento\CatalogSearch\Model\Search\FilterMapper\StockStatusFilter')) {
    include_once('Map/StockStatusFilter.php');
} else {
    class StockStatusFilter extends \Magento\CatalogSearch\Model\Search\FilterMapper\StockStatusFilter{}
}


