<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns\AdjustStock;

/**
 * Class Actions.
 *
 * @category Magestore
 * @package  Magestore_InventorySuccess
 * @module   Inventorysuccess
 * @author   Magestore Developer
 */
class Actions extends \Magestore\InventorySuccess\Ui\Component\Listing\Columns\Actions
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $_editUrl = 'inventorysuccess/adjuststock/edit';
}
