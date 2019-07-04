<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Renderer;

use Magento\Framework\DataObject;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface;
/**
 * Class Qty
 * @package Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Renderer
 */
class Location extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var WarehouseStockRegistryInterface
     */
    protected $warehouseStockRegistry;
    
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        WarehouseStockRegistryInterface $warehouseStockRegistry,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->warehouseStockRegistry = $warehouseStockRegistry;
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row) {
        $product_id = $row->getProductId();
        $products = $this->warehouseStockRegistry->getStockWarehouses($product_id);
        $totalWarehouse = 0;
        $content = '';
        foreach ($products as $product) {
            $totalWarehouse++;
            $url = $this->_urlBuilder->getUrl('inventorysuccess/warehouse/edit', ['id' => $product->getWarehouseId()]);
            $name = $product->getWarehouseName() . ' (' .$product->getWarehouseCode(). ')';
            $location = $product->getShelfLocation();
            if(!$location||$location=='')
                $location = __('N/A Location');
            $content .= '<a href="' . $url . '">'.$name.'</a>' . '<br/>' . '(' . $location . ')' . '<br/>';
        }
        if ($totalWarehouse > 5) {
            $contentScroll = '<div style="overflow-y:scroll; height: 110px;">' . $content . '</div>';
            return $contentScroll;
        }
        return $content;
    }
}
