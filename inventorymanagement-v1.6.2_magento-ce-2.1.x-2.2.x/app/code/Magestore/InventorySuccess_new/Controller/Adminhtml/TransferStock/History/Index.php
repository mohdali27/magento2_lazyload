<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\History;

use Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission;

class Index extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_InventorySuccess::inventory');
        $resultPage->getConfig()->getTitle()->prepend(__('Transfer Stock History'));
        return $resultPage;
    }
}
