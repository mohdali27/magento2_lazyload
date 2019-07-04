<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel\Conditions;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();

        $this->_init(
            \Amasty\Feed\Model\Conditions::class,
            \Amasty\Feed\Model\ResourceModel\Conditions::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
