<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Category;

class Mapping extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Amasty\Feed\Model\ResourceModel\Category\Mapping');
        $this->setIdFieldName('entity_id');
    }

    public function getCategoriesMappingCollection(\Amasty\Feed\Model\Category $category)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(
                'feed_category_id',
                $category->getId()
            );

        return $collection;
    }
}
