<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\SupplyNeeds\Edit\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class FormatNumber extends AbstractRenderer
{
     /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $col = $this->getColumn();
        if (!$row->getData($col->getIndex())) {
            return '';
        }
        $value = $row->getData($col->getIndex());
        return round($value, 2);
    }
}
