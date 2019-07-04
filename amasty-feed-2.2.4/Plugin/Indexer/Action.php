<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Plugin\Indexer;

use Amasty\Feed\Model\Indexer\Product\ProductFeedProcessor;
use Magento\Catalog\Model\Product\Action as ProductAction;

class Action
{
    /**
     * @var \Amasty\Feed\Model\Indexer\Product\ProductFeedProcessor
     */
    protected $productFeedProcessor;

    public function __construct(ProductFeedProcessor $productFeedProcessor)
    {
        $this->productFeedProcessor = $productFeedProcessor;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Action $object
     * @param \Magento\Catalog\Model\Product\Action $result
     *
     * @return \Magento\Catalog\Model\Product\Action
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterUpdateAttributes(ProductAction $object, ProductAction $result)
    {
        if (!$this->productFeedProcessor->getIndexer(ProductFeedProcessor::INDEXER_ID)->isScheduled()) {
            $this->productFeedProcessor->reindexList($result->getProductIds());
        }

        return $result;
    }
}
