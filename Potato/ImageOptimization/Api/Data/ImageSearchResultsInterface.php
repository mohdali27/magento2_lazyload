<?php

namespace Potato\ImageOptimization\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface ImageSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Potato\ImageOptimization\Api\Data\ImageInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Potato\ImageOptimization\Api\Data\ImageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get last page number
     * 
     * @return int
     */
    public function getLastPageNumber();

    /**
     * Set last page number
     *
     * @param int $count
     * @return $this
     */
    public function setLastPageNumber($count);
}
