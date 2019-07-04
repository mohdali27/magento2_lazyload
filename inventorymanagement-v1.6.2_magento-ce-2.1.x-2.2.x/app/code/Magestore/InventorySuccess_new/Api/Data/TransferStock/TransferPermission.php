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


interface TransferPermission
{
    const ALL_WAREHOUSE_SEND_REQUEST = "Magestore_InventorySuccess::all_warehouse";
    const REQUEST_STOCK_HISTORY = "Magestore_InventorySuccess::request_stock_history";
    const REQUEST_STOCK_VIEW = "Magestore_InventorySuccess::request_stock_view";
    const REQUEST_STOCK_CREATE = "Magestore_InventorySuccess::request_stock_create";
    const REQUEST_STOCK_ADD_PRODUCT = "Magestore_InventorySuccess::request_stock_add_product";
    const REQUEST_STOCK_ADD_DELIVERY = "Magestore_InventorySuccess::request_stock_add_delivery";
    const REQUEST_STOCK_ADD_RECEIVING = "Magestore_InventorySuccess::request_stock_add_receiving";
    const REQUEST_STOCK_COMPLETE = "Magestore_InventorySuccess::request_stock_complete";
    const REQUEST_STOCK_EDIT_PRODUCT = "Magestore_InventorySuccess::request_stock_edit_product";
    const REQUEST_STOCK_EDIT_GENERAL = "Magestore_InventorySuccess::request_stock_edit_general";

    const SEND_STOCK_HISTORY = "Magestore_InventorySuccess::send_stock_history";
    const SEND_STOCK_VIEW = "Magestore_InventorySuccess::send_stock_view";
    const SEND_STOCK_CREATE = "Magestore_InventorySuccess::send_stock_create";
    const SEND_STOCK_ADD_PRODUCT = "Magestore_InventorySuccess::send_stock_add_product";
    const SEND_STOCK_ADD_RECEIVING = "Magestore_InventorySuccess::send_stock_add_receiving";
    const SEND_STOCK_COMPLETE = "Magestore_InventorySuccess::send_stock_complete";
    const SEND_STOCK_DIRECT_TRANSFER = "Magestore_InventorySuccess::send_stock_direct_transfer";
    const SEND_STOCK_EDIT_GENERAL = "Magestore_InventorySuccess::send_stock_edit_general";

    const EXTERNAL_TRANSFER_STOCK = "Magestore_InventorySuccess::external_transfer_stock";
    const FROM_EXTERNAL_TRANSFER_STOCK_HISTORY = "Magestore_InventorySuccess::from_external_transfer_stock_history";
    const TO_EXTERNAL_TRANSFER_STOCK_HISTORY = "Magestore_InventorySuccess::to_external_transfer_stock_history";
    const EXTERNAL_TRANSFER_STOCK_VIEW = "Magestore_InventorySuccess::external_transfer_stock_view";
    const EXTERNAL_TRANSFER_STOCK_CREATE = "Magestore_InventorySuccess::external_transfer_stock_create";
    const EXTERNAL_TRANSFER_STOCK_ADD_PRODUCT = "Magestore_InventorySuccess::external_transfer_stock_add_product";
    const EXTERNAL_TRANSFER_STOCK_DIRECT_TRANSFER = "Magestore_InventorySuccess::external_transfer_stock_direct_transfer";
    const EXTERNAL_TRANSFER_STOCK_COMPLETE = "Magestore_InventorySuccess::external_transfer_stock_complete";
    const EXTERNAL_TRANSFER_STOCK_EDIT_GENERAL = "Magestore_InventorySuccess::external_transfer_stock_edit_general";
}