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

class Save extends \Solwin\Ournews\Controller\Adminhtml\News
{
    /**
     * Upload model
     *
     * @var \Solwin\Ournews\Model\Upload
     */
    protected $_uploadModel;

    /**
     * Image model
     *
     * @var \Solwin\Ournews\Model\News\Image
     */
    protected $_imageModel;

    /**
     * Date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * constructor
     *
     * @param \Solwin\Ournews\Model\Upload $uploadModel
     * @param \Solwin\Ournews\Model\News\Image $imageModel
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Solwin\Ournews\Model\NewsFactory $newsFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Solwin\Ournews\Model\Upload $uploadModel,
        \Solwin\Ournews\Model\News\Image $imageModel,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Solwin\Ournews\Model\NewsFactory $newsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_uploadModel    = $uploadModel;
        $this->_imageModel     = $imageModel;
        $this->_dateFilter     = $dateFilter;
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
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('news');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->filterData($data);
            $news = $this->initNews();
            $news->setData($data);


            if($_FILES['image']['error'] == 0) {
                $imageFile = $_FILES['image']['name'];
                $allowedImageExt =  ['jpg', 'jpeg', 'gif', 'png'];
                $fileExt = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));
                if (in_array($fileExt, $allowedImageExt)) {
                    $image = $this->_uploadModel
                            ->uploadFileAndGetName(
                                    'image',
                                    $this->_imageModel->getBaseDir(),
                                    $data);
                    $news->setImage($image);
                } else {
                    $this->messageManager
                        ->addError(__('Please upload a valid image. (jpg, jpeg, gif, png)'));
                    $resultRedirect->setPath($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                }
            } else if (isset($data['image']['delete'])) {
                $news->setImage('');
            } else if (isset($data['image']['value'])) {
                $news->setImage($data['image']['value']);
            }

            $this->_eventManager->dispatch(
                'solwin_ournews_news_prepare_save',
                [
                    'news' => $news,
                    'request' => $this->getRequest()
                ]
            );
            /*
             * check url key is emty and if urlkey is empty then generate urlkey
             */
            if ($data['url_key']=='') {
                $data['url_key'] = $data['title'];
            }
            $urlKey = $this->_objectManager
                    ->create('Magento\Catalog\Model\Product\Url')
                    ->formatUrlKey($data['url_key']);
            $data['url_key'] = $urlKey;

            $news->setUrlKey($urlKey);
            try {
                $news->save();
                $this->messageManager
                        ->addSuccess(__('News Details has been saved.'));
                $this->_session->setSolwinOurnewsNewsData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'solwin_ournews/*/edit',
                        [
                            'news_id' => $news->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('solwin_ournews/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager
                        ->addException($e,
                            __('Something went wrong while saving the News.'));
            }
            $this->_getSession()->setSolwinOurnewsNewsData($data);
            $resultRedirect->setPath(
                'solwin_ournews/*/edit',
                [
                    'news_id' => $news->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('solwin_ournews/*/');
        return $resultRedirect;
    }

    /**
     * filter values
     *
     * @param array $data
     * @return array
     */
    protected function filterData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            [
                'start_publish_date' => $this->_dateFilter,
                'end_publish_date' => $this->_dateFilter,
            ],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }
}