<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Indexer;

use \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;

abstract class AbstractIndexBuilder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var FeedCollectionFactory
     */
    protected $feedCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var array
     */
    protected $validProducts = [];

    /**
     * @var \Amasty\Feed\Model\Config
     */
    protected $config;

    public function __construct(
        FeedCollectionFactory $feedCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\Config $config
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param \Amasty\Feed\Model\Feed $feed
     * @param array $ids
     *
     * @return array
     */
    protected function applyRule(\Amasty\Feed\Model\Feed $feed, array $ids = [])
    {
        $feed = $feed->prepareModelConditions();
        $itemsPerPage = $this->config->getItemsPerPage();
        $page = 0;

        return $this->validateProducts($feed, $itemsPerPage, ++$page, $ids);
    }

    /**
     * @param \Amasty\Feed\Model\Feed $feed
     * @param int $itemsPerPage
     * @param int $page
     * @param array $ids
     *
     * @return array
     */
    private function validateProducts(\Amasty\Feed\Model\Feed $feed, $itemsPerPage, $page, $ids)
    {
        $result = $feed->getValidProducts($page, $itemsPerPage, $ids);
        $this->validProducts[$feed->getId()] =
            array_merge($this->validProducts[$feed->getId()], $result['productsId'][$page]);

        if ($result['isLastPage']) {
            return $this->validProducts;
        } else {
            return $this->validateProducts($feed, $itemsPerPage, ++$page, $ids);
        }
    }

    /**
     * Full reindex
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    abstract protected function doReindexFull();

    /**
     * @return array
     */
    public function getValidProducts()
    {
        return $this->validProducts;
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName);
    }

    /**
     * @param \Exception $exception
     *
     * @return void
     */
    protected function critical($exception)
    {
        $this->logger->critical($exception);
    }

    /**
     * Get active rules
     *
     * @return \Amasty\Feed\Model\ResourceModel\Feed\Collection
     */
    protected function getActiveFeeds()
    {
        return $this->feedCollectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('is_template', 0);
    }

    /**
     * Get active rules
     *
     * @return \Amasty\Feed\Model\ResourceModel\Feed\Collection
     */
    protected function getAllFeeds()
    {
        return $this->feedCollectionFactory->create()
            ->addFieldToFilter('is_template', 0);
    }

    /**
     * Clean by feed ids
     *
     * @param array $feedIds
     *
     * @return void
     */
    protected function deleteByFeedIds($feedIds)
    {
        $query = $this->connection->deleteFromSelect(
            $this->connection
                ->select()
                ->from($this->getTable('amasty_feed_valid_products'), 'feed_id')
                ->distinct()
                ->where('feed_id IN (?)', $feedIds),
            $this->getTable('amasty_feed_valid_products')
        );
        $this->connection->query($query);
    }

    protected function truncateTable()
    {
        $this->connection->truncateTable($this->getTable('amasty_feed_valid_products'));
    }

    /**
     * Clean by product ids
     *
     * @param array $productIds
     *
     * @return void
     */
    protected function deleteByProductIds($productIds)
    {
        $query = $this->connection->deleteFromSelect(
            $this->connection
                ->select()
                ->from($this->getTable('amasty_feed_valid_products'), 'valid_product_id')
                ->distinct()
                ->where('valid_product_id IN (?)', $productIds),
            $this->getTable('amasty_feed_valid_products')
        );
        $this->connection->query($query);
    }

    /**
     * @param \Amasty\Feed\Model\Feed $feed
     * @param null|array $validProducts
     *
     * @return void
     */
    protected function updateFeedProductIds(\Amasty\Feed\Model\Feed $feed, $validProducts = null)
    {
        $feedId = $feed->getId();
        $newValidIds = null;

        if ($validProducts) {
            $newValidIds = $validProducts[$feedId];
        } elseif ($this->getValidProducts() && array_key_exists($feedId, $this->getValidProducts())) {
            $newValidIds = $this->getValidProducts()[$feedId];
        }

        if ($newValidIds) {
            $this->connection->insertMultiple(
                $this->resource->getTableName('amasty_feed_valid_products'),
                $newValidIds
            );
        }
    }
}
