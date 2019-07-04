<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\History;

use Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission;

class FromExternal extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = TransferPermission::FROM_EXTERNAL_TRANSFER_STOCK_HISTORY;
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_InventorySuccess::transfer_from_external_history');
        $resultPage->getConfig()->getTitle()->prepend(__('Transfer Stock from External Location History'));
        return $resultPage;
    }
}
