<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Indexer\Feed;

use \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;
use Amasty\Feed\Model\Feed;

class IndexBuilder extends \Amasty\Feed\Model\Indexer\AbstractIndexBuilder
{
    /**
     * Reindex by id
     *
     * @param int $feedId
     *
     * @return void
     * @api
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexByFeedId($feedId)
    {
        $this->reindexByFeedIds([$feedId]);
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexByFeedIds(array $ids)
    {
        try {
            $this->doReindexByFeedIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     *
     * @return void
     * @throws \Exception
     */
    protected function doReindexByFeedIds($ids)
    {
        $this->deleteByFeedIds($ids);
        /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $collection */
        $collection = $this->getAllFeeds();
        $collection->addFieldToFilter('entity_id', ['in' => $ids]);

        /** @var \Amasty\Feed\Model\Feed $feed */
        foreach ($collection->getItems() as $feed) {
            $this->validProducts[$feed->getId()] = [];
            $this->applyRule($feed);
            $this->updateFeedProductIds($feed);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doReindexFull()
    {
        $this->truncateTable();

        /** @var \Amasty\Feed\Model\Feed $feed */
        foreach ($this->getAllFeeds() as $feed) {
            $this->validProducts[$feed->getId()] = [];
            $this->applyRule($feed);
            $this->updateFeedProductIds($feed);
        }
    }
}
