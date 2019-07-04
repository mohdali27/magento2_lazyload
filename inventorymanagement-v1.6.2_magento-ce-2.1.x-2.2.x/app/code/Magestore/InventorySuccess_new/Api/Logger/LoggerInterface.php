<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Logger;


interface LoggerInterface
{
    /**
     * 
     * @param string $message
     * @param string $section
     */
    public function log($message, $section = null);
}