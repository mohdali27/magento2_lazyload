<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Renderer;

use Magento\Framework\DataObject;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
/**
 * Class Qty
 * @package Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Renderer
 */
class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var WarehouseStockRegistryInterface
     */
    protected $warehouseStockRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        WarehouseStockRegistryInterface $warehouseStockRegistry,
        StoreManagerInterface $storemanager,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->_storeManager = $storemanager;
        $this->warehouseStockRegistry = $warehouseStockRegistry;
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row) {
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $imageUrl = $mediaDirectory.'catalog/product'.$this->_getValue($row);
        if($this->_getValue($row)){
            return '<img src="'.$imageUrl.'" width="50"/>';            
        }else{
            return null;
        }
    }
}
