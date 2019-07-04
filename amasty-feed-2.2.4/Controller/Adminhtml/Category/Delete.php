<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

use Magento\Framework\Exception\LocalizedException;

class Delete extends \Amasty\Feed\Controller\Adminhtml\Category
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Amasty\Feed\Model\Category');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the category.'));
                $this->_redirect('amfeed/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the category right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a category to delete.'));
        $this->_redirect('amfeed/*/');
    }
}
