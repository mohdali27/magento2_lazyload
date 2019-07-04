<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\External;

use Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission;

class NewAction extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{

    const ADMIN_RESOURCE = TransferPermission::EXTERNAL_TRANSFER_STOCK_CREATE;

    public function execute()
    {

        $resultForward = $this->_resultForwardFactory->create();
        $resultForward->forward('edit');
        return $resultForward;

    }

}


