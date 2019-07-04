<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab;

/**
 * Class DeleteProduct
 * @package Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab
 */
class DeleteProduct extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::warehouse/stock/delete/container.phtml';

    /**
     * @var \Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Stock\Grid
     */
    protected $blockGrid;

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
                'Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\DeleteProduct\Grid',
                'warehouse.delete.product.grid'
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
}