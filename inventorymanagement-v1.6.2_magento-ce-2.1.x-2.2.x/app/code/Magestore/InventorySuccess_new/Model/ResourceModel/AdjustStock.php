<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel;

class AdjustStock extends AbstractResource
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('os_adjuststock', 'adjuststock_id');
    }

    /**
     * Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {

        if (!$this->isValidPostData($object)) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Required field is null')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     *  Check whether post data is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPostData(\Magento\Framework\Model\AbstractModel $object)
    {
        if (is_null($object->getData('warehouse_id')) || is_null($object->getData('adjuststock_code')) || is_null($object->getData('reason'))) {
            return false;
        }
        return true;
    }
}
