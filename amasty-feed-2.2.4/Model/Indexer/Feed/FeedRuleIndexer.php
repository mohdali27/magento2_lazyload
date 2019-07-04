<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Indexer\Feed;

use Amasty\Feed\Model\Indexer\AbstractIndexer;

class FeedRuleIndexer extends AbstractIndexer
{

    /**
     * Override constructor. Indexer is changed
     *
     * @param \Amasty\Feed\Model\Indexer\Feed\IndexBuilder $indexBuilder
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Amasty\Feed\Model\Indexer\Feed\IndexBuilder $indexBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($indexBuilder, $eventManager);
        $this->indexBuilder = $indexBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByFeedIds(array_unique($ids));
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexByFeedId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [
            \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
        parent::executeFull();
    }
}
