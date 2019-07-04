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


class Email
{
    const EMAIL_TEMPLATE_TRANSFERSTOCK_CREATE = "transferstock_create";
    const EMAIL_TEMPLATE_TRANSFERSTOCK_CREATE_ATTACH_FILE = "transferstock_create_attach_file";
    const EMAIL_TEMPLATE_TRANSFERSTOCK_DELIVERY = "transferstock_delivery";
    const EMAIL_TEMPLATE_TRANSFERSTOCK_RECEIVING = "transferstock_receiving";
    const EMAIL_TEMPLATE_TRANSFERSTOCK_DIRECT_TRANSFER = "transferstock_direct_transfer";
    const EMAIL_TEMPLATE_TRANSFERSTOCK_RETURN = "transferstock_return";
}