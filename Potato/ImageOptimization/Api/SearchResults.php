<?php

namespace Potato\ImageOptimization\Api;

class SearchResults extends \Magento\Framework\Api\SearchResults
{
    const KEY_LAST_PAGE_NUMBER = 'last_page_number';
    
    /**
     * @return int
     */
    public function getLastPageNumber()
    {
        return $this->_get(self::KEY_LAST_PAGE_NUMBER);
    }

    /**
     * @param int $count
     * @return $this
     */
    public function setLastPageNumber($count)
    {
        return $this->setData(self::KEY_LAST_PAGE_NUMBER, $count);
    }
}