<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset;

/**
 * Class Stock
 * @package Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab
 */
class Product extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::managestock/stock/fieldset/product.phtml';

    /**
     * @var \Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Stock\Grid
     */
    protected $blockGrid;
    
    /**
     * @var \Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Stock\Grid
     */
    protected $inforGrid;

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Grid',
                'warehouse.stock.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInforGrid()
    {
        if (null === $this->inforGrid) {
            $this->inforGrid = $this->getLayout()->createBlock(
                'Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Infor',
                'warehouse.product.infor'
            );
        }
        return $this->inforGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getInforHtml()
    {
        return $this->getInforGrid()->toHtml();
    }
}