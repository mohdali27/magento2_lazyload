<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

class Edit extends \Amasty\Feed\Controller\Adminhtml\Category
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Amasty\Feed\Model\Category');

        if ($id) {
            $model->load($id);
            if (!$model->getFeedCategoryId()) {
                $this->messageManager->addErrorMessage(__('This categories no longer exists.'));

                return $this->_redirect('amfeed/*');
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_coreRegistry->register('current_amfeed_category', $model);

        $this->_view->loadLayout();

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getFeedCategoryId() ? $model->getName() : __('New Categories Mapping')
        );

        $this->_view->renderLayout();
    }
}
