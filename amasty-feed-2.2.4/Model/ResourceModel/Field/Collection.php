<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel\Field;

use Magento\Framework\DB\Select;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();

        $this->_init(
            \Amasty\Feed\Model\Field::class,
            \Amasty\Feed\Model\ResourceModel\Field::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @return $this
     */
    public function getSortedCollection()
    {
        $this->addOrder('name');

        return $this;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function getCustomConditions($fields)
    {
        $where = $this->_translateCondition('code', ['in' => $fields]);

        $this->getSelect()->reset(Select::COLUMNS)->joinInner(
            ['cond' => $this->getTable('amasty_feed_field_conditions')],
            'cond.feed_field_id = main_table.feed_field_id',
            ['cond.entity_id', 'main_table.code']
        )->where($where);

        return $this->getData();
    }
}
