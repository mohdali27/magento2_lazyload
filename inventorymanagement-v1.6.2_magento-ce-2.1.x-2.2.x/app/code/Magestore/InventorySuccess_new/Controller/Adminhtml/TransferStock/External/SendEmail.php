<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\External;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;

class SendEmail extends \Magento\Backend\App\Action
{


    /** @var  \Magestore\InventorySuccess\Model\TransferStock\Email\EmailNotificationFactory */
    protected $_emailNotificationFactory;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Model\TransferStock\Email\EmailNotificationFactory $emailNotificationFactory
    ) {
        $this->_emailNotificationFactory = $emailNotificationFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->_request->getParam("id");
        $whId = $this->_request->getParam("whId");
        $type = $this->_request->getParam("type");
        $emailNotification = $this->_emailNotificationFactory->create();
        $emailNotification->notifyCreateNewTransferOmniChannel($id,$whId);
        $resultRedirect = $this->resultRedirectFactory->create();
        if($type){
            return $resultRedirect->setPath('*/*/edit', ['type' => $type, 'id' => $id,'_current' => true]);
        }
        return $resultRedirect->setPath('*/*/edit', ['id' => $id,'_current' => true]);
    }

}


