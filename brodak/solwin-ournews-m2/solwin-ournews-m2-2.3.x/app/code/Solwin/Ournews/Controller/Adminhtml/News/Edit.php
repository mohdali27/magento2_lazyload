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

use Magento\Framework\Controller\Result\JsonFactory;

class Edit extends \Solwin\Ournews\Controller\Adminhtml\News
{

    /**
     * Page factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * constructor
     * 
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param \Solwin\Ournews\Model\NewsFactory $newsFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        \Solwin\Ournews\Model\NewsFactory $newsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct(
                $newsFactory,
                $registry,
                $context
                );
    }

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
     * @return \Magento\Backend\Model\View\Result\Page|
     * \Magento\Backend\Model\View\Result\Redirect|
     * \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('news_id');
        /**
         * @var \Solwin\Ournews\Model\News $news
         */
        $news = $this->initNews();
        /**
         * @var \Magento\Backend\Model\View\Result\Page|
         * \Magento\Framework\View\Result\Page $resultPage
         */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Solwin_Ournews::news');
        $resultPage->getConfig()->getTitle()->set(__('News List'));
        if ($id) {
            $news->load($id);
            if (!$news->getId()) {
                $this->messageManager
                        ->addError(__('This News no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'solwin_ournews/*/edit',
                    [
                        'news_id' => $news->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $news->getId() ? $news->getTitle() : __('New News Details');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_session
                ->getData('solwin_ournews_news_data', true);
        if (!empty($data)) {
            $news->setData($data);
        }
        return $resultPage;
    }
}