<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Indexer\Product;

use Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;

class IndexBuilder extends \Amasty\Feed\Model\Indexer\AbstractIndexBuilder
{
    /**
     * @var \Amasty\Feed\Model\Indexer\Feed\IndexBuilder
     */
    private $feedBuilder;

    public function __construct(
        FeedCollectionFactory $feedCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\Config $config,
        \Amasty\Feed\Model\Indexer\Feed\IndexBuilder $feedBuilder
    ) {
        parent::__construct($feedCollectionFactory, $resource, $logger, $config);
        $this->feedBuilder = $feedBuilder;
    }

    /**
     * Reindex by id
     *
     * @param int $productId
     *
     * @return void
     * @api
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexByProductId($productId)
    {
        $this->reindexByProductIds([$productId]);
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
    public function reindexByProductIds(array $ids)
    {
        try {
            $this->doReindexByProductIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doReindexFull()
    {
        $this->feedBuilder->reindexFull();
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     *
     * @return void
     * @throws \Exception
     */
    protected function doReindexByProductIds($ids)
    {
        $this->deleteByProductIds($ids);
        foreach ($this->getActiveFeeds() as $feed) {
            $this->validProducts[$feed->getId()] = [];
            $validProducts = $this->applyRule($feed, $ids);
            $this->updateFeedProductIds($feed, $validProducts);
        }
    }
}
