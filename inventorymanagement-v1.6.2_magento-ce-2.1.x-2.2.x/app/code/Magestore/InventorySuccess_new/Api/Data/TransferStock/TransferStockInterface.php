<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 09/08/2016
 * Time: 14:24
 */

namespace Magestore\InventorySuccess\Api\Data\TransferStock;


interface TransferStockInterface
{
    const STATUS_PENDING = "pending";
    const STATUS_PROCESSING = "processing";
    const STATUS_COMPLETED = "completed";
    const STATUS_CANCEL = "cancel";

    const TYPE_REQUEST = "request";
    const TYPE_SEND = "send";
    const TYPE_TO_EXTERNAL = "to_external";
    const TYPE_FROM_EXTERNAL = "from_external";
    const TRANSFER_CODE_PREFIX="TRA";

    const PERMISSION_REQUEST_STOCK = "Magestore_InventorySuccess::request_stock";
    const PERMISSION_REQUEST_STOCK_CREATE = "Magestore_InventorySuccess::request_stock_create";
    const PERMISSION_REQUEST_STOCK_ADD_PRODUCT = "Magestore_InventorySuccess::request_stock_add_product";
    const PERMISSION_REQUEST_STOCK_ADD_DELIVERY = "Magestore_InventorySuccess::request_stock_add_delivery";
    const PERMISSION_REQUEST_STOCK_ADD_RECEIVING = "Magestore_InventorySuccess::request_stock_add_receiving";
    const PERMISSION_REQUEST_STOCK_COMPLETE = "Magestore_InventorySuccess::request_stock_complete";
}