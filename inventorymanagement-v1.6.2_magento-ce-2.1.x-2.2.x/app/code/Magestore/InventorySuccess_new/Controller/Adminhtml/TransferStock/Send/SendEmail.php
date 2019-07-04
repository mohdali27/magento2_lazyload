<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Send;
use Magento\Framework\App\Filesystem\DirectoryList;
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
        $emailNotification = $this->_emailNotificationFactory->create();
        $emailNotification->notifyCreateNewTransferOmniChannel($id,$whId);
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/edit', ['id' => $id,'_current' => true]);
    }

}


