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

abstract class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * JSON Factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonFactory;

    /**
     * News Factory
     * 
     * @var \Solwin\Ournews\Model\NewsFactory
     */
    protected $_newsFactory;

    /**
     * constructor
     * 
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Solwin\Ournews\Model\NewsFactory $newsFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Solwin\Ournews\Model\NewsFactory $newsFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_jsonFactory = $jsonFactory;
        $this->_newsFactory = $newsFactory;
        parent::__construct($context);
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
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->_jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        foreach (array_keys($postItems) as $newsId) {
            /** @var \Solwin\Ournews\Model\News $news */
            $news = $this->_newsFactory->create()->load($newsId);
            try {
                $newsData = $postItems[$newsId];
                $news->addData($newsData);
                $news->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithNewsId($news,
                        $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithNewsId($news,
                        $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithNewsId(
                    $news,
                    __('Something went wrong while saving the News.')
                );
                $error = true;
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add News id to error message
     *
     * @param \Solwin\Ournews\Model\News $news
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithNewsId(
        \Solwin\Ournews\Model\News $news, $errorText
    ) {
        return '[News ID: ' . $news->getId() . '] ' . $errorText;
    }
}
