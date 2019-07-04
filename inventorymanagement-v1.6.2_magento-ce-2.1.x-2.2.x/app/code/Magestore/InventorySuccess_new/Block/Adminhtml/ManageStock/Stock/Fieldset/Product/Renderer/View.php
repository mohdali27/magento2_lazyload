<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Renderer;

use Magento\Framework\DataObject;

/**
 * Class Qty
 * @package Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Renderer
 */
class View extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row) {
        $product_id = $row->getProductId();
        return '<a class="view_infor" product-name="'.$row->getName().' ('.$row->getSku().')'.'" value="'.$product_id.'">'.__('View').'</a>';
    }
}
