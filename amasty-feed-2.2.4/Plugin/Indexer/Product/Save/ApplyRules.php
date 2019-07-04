<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Plugin\Indexer\Product\Save;

use Amasty\Feed\Model\Indexer\Product\ProductFeedProcessor;

class ApplyRules
{
    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    private $product;

    /**
     * @var \Amasty\Feed\Model\Indexer\Product\ProductFeedProcessor
     */
    private $productFeedProcessor;

    public function __construct(ProductFeedProcessor $productFeedProcessor)
    {
        $this->productFeedProcessor = $productFeedProcessor;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Magento\Framework\Model\AbstractModel $product
     */
    public function beforeSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Framework\Model\AbstractModel $product
    ) {
        $this->product = $product;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param $result
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave(\Magento\Catalog\Model\ResourceModel\Product $subject, $result) {
        if (!$this->product->getIsMassupdate()
            && !$this->productFeedProcessor->getIndexer(ProductFeedProcessor::INDEXER_ID)->isScheduled()
        ) {
            $this->productFeedProcessor->reindexRow($this->product->getId());
        }

        return $result;
    }
}
