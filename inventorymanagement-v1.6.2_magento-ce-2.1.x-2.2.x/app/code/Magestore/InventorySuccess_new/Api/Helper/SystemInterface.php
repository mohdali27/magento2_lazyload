<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Helper;

use Magento\User\Api\Data\UserInterface;

interface SystemInterface
{
    /**
     * Get current timestamp
     * 
     * @return string
     */
    public function getCurTime();
    
    /**
     * Get current unix time stamp
     * 
     * @return int
     */
    public function getUnixTime();
    
    
    /**
     * Get current admin user
     * 
     * @return UserInterface
     */
    public function getCurUser();
}