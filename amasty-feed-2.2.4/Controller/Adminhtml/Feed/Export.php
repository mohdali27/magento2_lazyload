<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $logger);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Amasty\Feed\Model\Feed');

        if ($id) {
            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addErrorMessage(__('This feed no longer exists.'));
                $this->_redirect('amfeed/*');
                return;
            }

            try {
                $this->fileFactory->create(
                    $model->getFilename(),
                    $model->export(),
                    DirectoryList::VAR_DIR,
                    $model->getContentType()
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while export feed data. Please review the error log.')
                );
                $this->logger->critical($e);
            }

            $this->_redirect('amfeed/*/edit', ['id' => $id]);
        }
    }
}
