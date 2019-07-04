<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Dashboard;

class Sales extends \Magestore\InventorySuccess\Block\Adminhtml\Chart\Type\AbstractLineChart
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::warehouse/dashboard/sales.phtml';

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Order\Item\Collection
     */
    protected $_salesReportLast30days;

    /**
     * @var string
     */
    protected $_orderQty30days;

    /**
     * @var string
     */
    protected $_itemQty30days;

    /**
     * @var string
     */
    protected $_revenue30days;

    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setContainerId('sales_chart_container');
        $this->setTitle('Sales');
        $this->setSubtitle('Last 30 Days Sales Reports');
        $this->getSalesReportLast30days();
        $this->getTotalOrderItemLast30days();
    }

    public function getCurrencySymbol()
    {
        $currency = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magento\Directory\Model\Currency');
        $currency->load(current($currency->getConfigBaseCurrencies()));
        return $currency->getCurrencySymbol();
    }

    public function getSalesReportLast30days(){
        if(!$this->_salesReportLast30days){
            $this->_salesReportLast30days = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Magestore\InventorySuccess\Model\ResourceModel\Sales\Order\Item\Collection')
                ->getSalesReport($this->getRequest()->getParam('id'), 30);
        }
        return $this->_salesReportLast30days;
    }

    public function getTotalOrderItemLast30days(){
        $collection = $this->_salesReportLast30days;
        $collection->getTotalOrderItem();
        $data = $collection->getData();
        $totalByDay = [];
        foreach ($data as $item) {
            $totalByDay[$item['date_without_hour']] = $item;
        }
        return $this->addSalesChartData($totalByDay);
    }

    public function addSalesChartData($totalByDay){
        $this->_orderQty30days = '';
        $this->_itemQty30days = '';
        $this->_revenue30days = '';
        for ($i = 30; $i >= 0; $i--) {
            $d = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\Stdlib\DateTime\DateTime')
                ->gmtDate('Y-m-d', strtotime('-'.$i.' days'));
            if ($i != 30){
                $this->_orderQty30days .= ', ';
                $this->_itemQty30days .= ', ';
                $this->_revenue30days .= ', ';
            }if (isset($totalByDay[$d])) {
                $this->_orderQty30days .= round($totalByDay[$d]['order_by_day'], 2);
                $this->_itemQty30days .= round($totalByDay[$d]['item_qty_by_day'], 2);
                $this->_revenue30days .= round($totalByDay[$d]['revenue_by_day'], 2);
            } else {
                $this->_orderQty30days .= '0';
                $this->_itemQty30days .= '0';
                $this->_revenue30days .= '0';
            }
        }
        return $this;
    }

    public function getOrderQty30days(){
        return $this->_orderQty30days;
    }

    public function getItemQty30days(){
        return $this->_itemQty30days;
    }

    public function getRevenue30days(){
        return $this->_revenue30days;
    }
}
