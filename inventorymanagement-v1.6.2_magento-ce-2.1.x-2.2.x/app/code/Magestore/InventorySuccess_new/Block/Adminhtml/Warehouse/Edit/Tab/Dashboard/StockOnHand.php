<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Dashboard;

class StockOnHand extends \Magestore\InventorySuccess\Block\Adminhtml\Chart\Type\AbstractColumnChart
{
    const NUMBER_PRODUCT = 10;
    
    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setContainerId('stock_on_hand_container');
        $this->setTitle('Stock On Hand');
        $this->setSubtitle('Stock On Hand Reports');
        $this->setYAxisTitle('Values');
        $this->setTooltip('Total Qty: '.'<b>{point.y}</b>');
        $this->setSeriesName(['On-Hand Qty']);
        $this->setSeriesDataLabel([['format'=>'{point.y}']]);
        $this->getStockOnHand();
    }
    
    protected function getStockOnHand(){
        $stockCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\Collection');
        $stockCollection->getHighestQtyProducts(self::NUMBER_PRODUCT, $this->getRequest()->getParam('id'));
        $seriesData = [];
        $data = [];
        foreach ($stockCollection as $item){
            $data[] = [$item->getSku(), floatval($item->getTotalQty())];
        }
        $seriesData[] = $data;
        $this->setSeriesData($seriesData);
        return $this;
    }
}