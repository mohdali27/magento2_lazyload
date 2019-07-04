<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;

/**
 * Class Index
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
class Index extends \Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking\Stocktaking
{
    /**
     * History action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_InventorySuccess::stocktaking_history');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Stocktaking'));
        $resultPage->addBreadcrumb(__('Inventory Success'), __('Inventory Success'));
        $resultPage->addBreadcrumb(__('Manage Adjust Stock'), __('Manage Stocktaking'));

        return $resultPage;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_InventorySuccess::stocktaking_history');
    }

}
