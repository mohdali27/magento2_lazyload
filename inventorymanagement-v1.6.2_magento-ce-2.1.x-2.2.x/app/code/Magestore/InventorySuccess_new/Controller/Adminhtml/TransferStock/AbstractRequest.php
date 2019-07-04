<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock;

abstract class AbstractRequest extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractTransfer
{
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
    ){
        parent::__construct($context);
    }
}
