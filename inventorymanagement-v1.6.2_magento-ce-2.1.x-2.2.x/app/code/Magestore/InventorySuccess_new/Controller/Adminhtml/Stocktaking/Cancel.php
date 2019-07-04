<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;

/**
 * Class Edit
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
class Cancel extends \Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking\Stocktaking
{
    /**
     * Edit Store.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $id = $this->getRequest()->getParam('id');
        /** @var \Magestore\InventorySuccess\Model\Stocktaking $model */
        $model = $this->stocktakingFactory->create();
        if ($id) {
            $this->stocktakingResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This stocktaking no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/edit',['id' => $id]);
    }
}
