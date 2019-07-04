<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel;

class ValidProducts extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('amasty_feed_valid_products', 'entity_id');
    }

    /**
     * @param \Amasty\Feed\Model\ValidProducts $model
     * @param $feedId
     *
     * @return \Amasty\Feed\Model\ValidProducts
     */
    public function getByFeedId(\Amasty\Feed\Model\ValidProducts $model, $feedId)
    {
        $this->load($model, $feedId, 'feed_id');

        return $model;
    }
}
