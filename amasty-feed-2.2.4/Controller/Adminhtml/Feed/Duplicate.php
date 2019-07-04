<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Model\Indexer\Feed\FeedRuleProcessor;

class Duplicate extends \Amasty\Feed\Controller\Adminhtml\Feed\AbstractMassAction
{
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
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory $collectionFactory,
        FeedRuleProcessor $feedRuleProcessor
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $resultLayoutFactory,
            $logger,
            $feedCopier,
            $filter,
            $collectionFactory
        );

        $this->feedRuleProcessor = $feedRuleProcessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function massAction($collection)
    {
        $feedIds = [];

        foreach($collection as $model)
        {
            $newModel = $this->feedCopier->copy($model);
            $this->messageManager->addSuccessMessage(__('Feed %1 was duplicated', $model->getName()));
            $feedIds[]= $newModel->getId();
        }

        if (!$this->feedRuleProcessor->getIndexer(FeedRuleProcessor::INDEXER_ID)->isScheduled()) {
            $this->feedRuleProcessor->reindexList($feedIds);
        }
    }
}
