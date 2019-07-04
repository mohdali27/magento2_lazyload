<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Source\Adminhtml;

/**
 * Class ImportType
 * @package Magestore\InventorySuccess\Model\Source\Adminhtml
 */
class ImportType
{
    const TYPE_ADJUST_STOCK = 1;

    const TYPE_TRANSFER_STOCK_TO_REQUEST = 2;

    const TYPE_TRANSFER_STOCK_TO_TRANSFER_DELIVERY = 3;

    const TYPE_TRANSFER_STOCK_TO_TRANSFER_RECEIVING = 4;

    const TYPE_TRANSFER_STOCK_TO_TRANSFER_SEND = 5;

    const TYPE_TRANSFER_STOCK_TO_TRANSFER_EXTERNAL_FROM = 6;

    const TYPE_TRANSFER_STOCK_TO_TRANSFER_EXTERNAL_TO = 7;

    const TYPE_TRANSFER_STOCK_TO_TRANSFER_SEND_RECEIVING = 8;

    const TYPE_STOCKTAKING = 9;

}

