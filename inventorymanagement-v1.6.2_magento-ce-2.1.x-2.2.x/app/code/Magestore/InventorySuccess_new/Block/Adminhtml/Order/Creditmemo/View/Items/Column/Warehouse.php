<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Order\Creditmemo\View\Items\Column;


class Warehouse extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
    /**
     * @var \Magestore\InventorySuccess\Model\OrderProcess\DataProvider\CreditmemoView
     */
    protected $creditmemoViewDataProvider;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;    
    
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
        \Magestore\InventorySuccess\Model\OrderProcess\DataProvider\CreditmemoView $creditmemoViewDataProvider,
        array $data = []
    ) {
        $this->creditmemoViewDataProvider = $creditmemoViewDataProvider;
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }
    
    /**
     * Get list of available warehouses to return items
     * 
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getReturnWarehouse($item)
    {   
        if($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            foreach($item->getChildrenItems() as $cItem) {
                $creditmemoItem = $this->getCreditmemo()->getItemByOrderId($cItem->getId());
            }
        } else {
            $creditmemoItem = $this->getCreditmemo()->getItemByOrderId($item->getId());        
        }
        
        return $this->creditmemoViewDataProvider->getReturnWarehouse($creditmemoItem->getId());
    }
    
    /**
     * 
     * @param \Magestore\InventorySuccess\Model\Warehouse $warehouse
     * @return string
     */
    public function getWarehouseDisplay($warehouse)
    {
        $html = '';
        if($warehouse->getId()) {
            $html .= $warehouse->getWarehouseName();
            $html .= ' (<a href="'.  $this->urlBuilder->getUrl('inventorysuccess/warehouse/edit', ['id' => $warehouse->getId()]) .'" target="_blank">';
            $html .= $warehouse->getWarehouseCode();
            $html .= '</a>)';
        }
        return $html;
    }    
    
}
