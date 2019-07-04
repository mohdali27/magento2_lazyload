<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created by PhpStorm.
 * User: zero
 * Date: 07/04/2016
 * Time: 09:42
 */

namespace Magestore\InventorySuccess\Model\OptionManage;


interface OptionInterface
    extends \Magento\Framework\Option\ArrayInterface,
    \Magestore\InventorySuccess\Model\OptionManage\OptionHashInterface
{
    /**
     * @return mixed
     */
    public function toOptionArray();

    /**
     * @return mixed
     */
    public function toOptionHash();

}