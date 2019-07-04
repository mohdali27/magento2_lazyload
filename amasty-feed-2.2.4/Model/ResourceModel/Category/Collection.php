<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel\Category;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Amasty\Feed\Model\Category', 'Amasty\Feed\Model\ResourceModel\Category');
    }

    /**
     * Add google setup filter
     *
     * @return $this
     */
    public function addGoogleSetupFilter()
    {
        $this->addFieldToFilter(
            'code',
            ['like' => "google_category_%"]
        );

        return $this;
    }
}
