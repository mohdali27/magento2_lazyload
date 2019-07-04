<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Webhook
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Controller\Adminhtml\ManageHooks;

use Mageplaza\Webhook\Controller\Adminhtml\AbstractManageHooks;

/**
 * Class Delete
 * @package Mageplaza\Webhook\Controller\Adminhtml\ManageHooks
 */
class Delete extends AbstractManageHooks
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $hook           = $this->initHook();
        if ($hook->getId()) {
            try {
                /** @var \Mageplaza\Webhook\Model\Hook $hook */
                $hook->delete();

                $this->messageManager->addSuccess(__('The Hook has been deleted.'));
                $resultRedirect->setPath('mpwebhook/*/');

                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                // go back to edit form
                $resultRedirect->setPath('mpwebhook/*/edit', ['hook_id' => $hook->getId()]);

                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('The Hook to delete was not found.'));

        $resultRedirect->setPath('mpwebhook/*/');

        return $resultRedirect;
    }
}
