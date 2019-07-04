<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab;

class Dashboard extends \Magento\Backend\Block\Template
{
    /**
     * @var int current warehouse id
     */
    protected $currentWarehouseId;

    /**
     * @var array all totals qty of current warehouse
     */
    protected $allTotalsQty;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\CollectionFactory
     */
    protected $warehouseStockCollectionFactory;
    
    /**
     * @var array
     */
    protected $localeFormat;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * Dashboard constructor.
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\CollectionFactory $warehouseStockCollectionFactory
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param array $data
     */
    public function __construct(
        
        \Magento\Backend\Block\Template\Context $context, 
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\CollectionFactory $warehouseStockCollectionFactory,
        \Magento\Framework\Locale\Format $localeFormat,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    ){
        $this->warehouseStockCollectionFactory = $warehouseStockCollectionFactory;
        $this->localeFormat = $localeFormat->getPriceFormat();
        $this->currencyFactory = $currencyFactory;
        parent::__construct($context, $data);
    }
    
    /**
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::warehouse/dashboard.phtml';

    /**
     * @var \Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Stock\Grid[]
     */
    protected $blockGrid = [];

    /**
     * Retrieve instance of grid block
     *
     * @param string $blockClass
     * @param string $blockName
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChart($blockClass, $blockName)
    {
        if (!isset($this->blockGrid[$blockName])) {
            $this->blockGrid[$blockName] = $this->getLayout()->createBlock(
                $blockClass, $blockName
            );
        }
        return $this->blockGrid[$blockName];
    }

    /**
     * Return HTML of grid block
     *
     * @param string $blockClass
     * @param string $blockName
     * @return string
     */
    public function getChartHtml($blockClass, $blockName)
    {
        return $this->getChart($blockClass, $blockName)->toHtml();
    }

    /**
     * @return int|null
     * 
     */
    protected function getCurrentWarehouseId(){
        if(!$this->currentWarehouseId)
            $this->currentWarehouseId = $this->getRequest()->getParam('id', null);
        return $this->currentWarehouseId;
    }

    /**
     * @return array
     */
    public function getAllTotalsQty(){
        if(!$this->allTotalsQty)
            $this->allTotalsQty = $this->warehouseStockCollectionFactory->create()
                ->getAllTotalsQty($this->getCurrentWarehouseId())
                ->getData();
        return $this->allTotalsQty;
    }

    /**
     * @return decimal
     */
    public function getSumTotalQty(){
        $allTotalQty = $this->getAllTotalsQty();
        return isset($allTotalQty['sum_total_qty'])?
            number_format(floatval($allTotalQty['sum_total_qty']), 0, $this->localeFormat['decimalSymbol'], $this->localeFormat['groupSymbol']):
            0;
    }

    /**
     * @return decimal
     */
    public function getSumQtyToShip(){
        $allTotalQty = $this->getAllTotalsQty();
        return isset($allTotalQty['sum_qty_to_ship'])?
            number_format(floatval($allTotalQty['sum_qty_to_ship']), 0, $this->localeFormat['decimalSymbol'], $this->localeFormat['groupSymbol']):
            0;
    }

    /**
     * @return decimal
     */
    public function getSumAvailableQty(){
        $allTotalQty = $this->getAllTotalsQty();
        return isset($allTotalQty['available_qty'])?
            number_format(floatval($allTotalQty['available_qty']), 0, $this->localeFormat['decimalSymbol'], $this->localeFormat['groupSymbol']):
            0;
    }

    public function getSalesReport(){
        $chart = $this->getChart(
            'Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Dashboard\Sales',
            'warehouse.best.seller.top'
        );
        return $chart;
    }
    
    protected function getTotalSales($totalString){
        $totalArray = explode(',', $totalString);
        return array_sum($totalArray);
    }
    
    public function getTotalOrderQty30days(){
        $orderQtyString = $this->getSalesReport()->getOrderQty30days();
        return number_format(
            $this->getTotalSales($orderQtyString), 
            0, 
            $this->localeFormat['decimalSymbol'], 
            $this->localeFormat['groupSymbol']
        );
    }
    
    public function getTotalItemQty30days(){
        $itemQtyString = $this->getSalesReport()->getItemQty30days();
        return number_format(
            $this->getTotalSales($itemQtyString),
            0,
            $this->localeFormat['decimalSymbol'],
            $this->localeFormat['groupSymbol']
        );
    }
    
    public function getTotalRevenue30days(){
        $revenueString = $this->getSalesReport()->getRevenue30days();
        $currency = $this->currencyFactory->create();
        $currency->load(current($currency->getConfigBaseCurrencies()));
        return $currency->format($this->getTotalSales($revenueString));
    }
}