<?php
/**
 * Solwin Infotech
 * Solwin Ournews Extension
 *
 * @category   Solwin
 * @package    Solwin_Ournews
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
?>
<?php

namespace Solwin\Ournews\Controller\Adminhtml\News;

class Delete extends \Solwin\Ournews\Controller\Adminhtml\News
{
    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Solwin_Ournews::news');
    }
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('news_id');
        if ($id) {
            $title = "";
            try {
                /**
                 * @var \Solwin\Ournews\Model\News $news
                 */
                $news = $this->_newsFactory->create();
                $news->load($id);
                $title = $news->getTitle();
                $news->delete();
                $this->messageManager
                        ->addSuccess(__('News Details has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_solwin_ournews_news_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                $resultRedirect->setPath('solwin_ournews/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_solwin_ournews_news_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('solwin_ournews/*/edit',
                        ['news_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('News Details was not found.'));
        // go to grid
        $resultRedirect->setPath('solwin_ournews/*/');
        return $resultRedirect;
    }
}