<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Model\Indexer\Feed\FeedRuleProcessor;

class FromTemplate extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    protected $feedCopier;
    protected $storeManager;

    /**
     * @var \Amasty\Feed\Model\Indexer\Feed\FeedRuleProcessor
     */
    private $feedRuleProcessor;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\Feed\Copier $feedCopier,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        FeedRuleProcessor $feedRuleProcessor
    ) {
        $this->feedCopier = $feedCopier;
        $this->storeManager = $storeManager;
        $this->feedRuleProcessor = $feedRuleProcessor;

        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $logger);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $storeId = $this->storeManager->getStore()->getId();

            $model = $this->_objectManager->create('Amasty\Feed\Model\Feed');

            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addErrorMessage(__('This feed no longer exists.'));
                $this->_redirect('amfeed/*');
                return;
            }

            /** @var \Amasty\Feed\Model\Feed $newModel */
            $newModel = $this->feedCopier->fromTemplate($model, $storeId);

            if (!$this->feedRuleProcessor->getIndexer(FeedRuleProcessor::INDEXER_ID)->isScheduled()) {
                $this->feedRuleProcessor->reindexRow($newModel->getId());
            }

            $this->messageManager->addSuccessMessage(__('Feed %1 created', $newModel->getName()));
            $this->_redirect('amfeed/*/edit', [
                'id' => $newModel->getId()
            ]);
            return;

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while export feed data. Please review the error log.')
            );
            $this->logger->critical($e);
        }

        $this->_redirect('amfeed/*');
    }
}
