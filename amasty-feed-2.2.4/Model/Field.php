<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

class Field extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Field::class);
        $this->setIdFieldName('feed_field_id');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }
}
