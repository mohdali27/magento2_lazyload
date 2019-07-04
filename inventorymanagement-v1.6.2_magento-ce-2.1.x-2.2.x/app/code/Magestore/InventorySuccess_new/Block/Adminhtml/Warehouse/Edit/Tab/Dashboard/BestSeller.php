<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Dashboard;

class BestSeller extends \Magestore\InventorySuccess\Block\Adminhtml\Chart\Type\AbstractColumnChart
{
    const NUMBER_PRODUCT = 5;
    
    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setContainerId('best_seller_container');
        $this->setTitle('Best Seller');
        $this->setSubtitle('Lifetime Best Seller');
        $this->setYAxisTitle('Values');
        $this->setTooltip('Total Qty: '.'<b>{point.y}</b>');
        $this->setSeriesName(['Shipped Qty']);
        $this->setSeriesDataLabel([['format'=>'{point.y}']]);
        $this->getBestSeller();
    }
    
    protected function getBestSeller(){
        $stockCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\Collection');
        $stockCollection->getBestSellerProducts(self::NUMBER_PRODUCT, $this->getRequest()->getParam('id'));
        $seriesData = [];
        $data = [];
        foreach ($stockCollection as $item){
            $qty = $item->getTotalQtyShipped();
            if(!$qty || $qty == '')
                $qty = 0;
            $data[] = [$item->getSku(), floatval($qty)];
        }
        
        $seriesData[] = $data;
        $this->setSeriesData($seriesData);
        return $this;
    }
}