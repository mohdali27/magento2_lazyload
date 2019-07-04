<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api;


interface IncrementIdManagementInterface
{
    
    /**
     * Get next increment Id
     * 
     * @param string $prefixCode
     * @return int
     */
    public function getNextId($prefixCode);
    
    /**
     * Generate next code number
     * 
     * @param string $prefixCode
     * @return string
     */
    public function getNextCode($prefixCode);
    
    /**
     * Update current increment Id
     * 
     * @param string $prefixCode
     * @param int $id
     */
    public function updateId($prefixCode, $id = null);

}