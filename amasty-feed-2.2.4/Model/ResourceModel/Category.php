<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel;

use Amasty\Feed\Model\Category as ModelCategory;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Category extends AbstractDb
{
    const TABLE_NAME = 'amasty_feed_category';

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'feed_category_id');
    }

    /**
     * @param ModelCategory $category
     * @param               $catId
     *
     * @return $this
     */
    public function loadByCategoryId(ModelCategory $category, $catId)
    {
        ($catId) ? $this->load($category, $catId) : $category->setData([]);

        return $this;
    }
}
