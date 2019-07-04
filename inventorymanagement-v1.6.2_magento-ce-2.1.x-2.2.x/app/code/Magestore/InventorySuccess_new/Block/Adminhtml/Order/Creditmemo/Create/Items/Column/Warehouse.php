<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Order\Creditmemo\Create\Items\Column;


class Warehouse extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
    /**
     * @var \Magestore\InventorySuccess\Model\OrderProcess\DataProvider\CreditmemoForm
     */
    protected $creditmemoFormDataProvider;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magestore\InventorySuccess\Model\OrderProcess\DataProvider\CreditmemoForm $creditmemoFormDataProvider,
        array $data = []
    ) {
        $this->creditmemoFormDataProvider = $creditmemoFormDataProvider;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }
    
    /**
     * Get list of available warehouses to return items
     * 
     * @return array
     */
    public function getAvailableWarehouses()
    {
        return $this->creditmemoFormDataProvider->getAvailableWarehouses();
    }
    
    /**
     * 
     * @param int $warehouseId
     * @param int $itemId
     * @return bool
     */
    public function isSelectedWarehouse($warehouseId, $itemId)
    {
        $creditMemoData = $this->_request->getParam('creditmemo');
        $selectedWarehouse = null;
        if(isset($creditMemoData['items'][$itemId]['warehouse']))
            $selectedWarehouse = $creditMemoData['items'][$itemId]['warehouse'];
        
        if($selectedWarehouse == $warehouseId) {
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isRequired($item)
    {
        $creditMemoData = $this->_request->getParam('creditmemo');
        $refundQty = 0;
        if(isset($creditMemoData['items'][$item->getItemId()]['qty'])) {
            $refundQty = $creditMemoData['items'][$item->getItemId()]['qty'];
        } else {
            $refundQty = $item->getQtyToRefund();
        }
        if(!$refundQty) {
            return false;
        }
        return true;
    }
    
    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function isShow($item)
    {
        $creditMemoData = $this->_request->getParam('creditmemo');
        $refundQty = 0;
        if(isset($creditMemoData['items'][$item->getItemId()]['qty'])) {
            $refundQty = $creditMemoData['items'][$item->getItemId()]['qty'];
        } else {
            $refundQty = $item->getQtyToRefund();
        }
        
        if(!$refundQty) {
            return false;
        }
        return true;        
    }
}
