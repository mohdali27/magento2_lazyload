<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Logger;

use Magestore\InventorySuccess\Api\Logger\LoggerInterface;

class Logger implements LoggerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger;
    
    const MESSAGE_PREFIX = 'inventorysuccess';
    
    const LOG_LEVEL = 'debug';
    
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * 
     * @param string $message
     * @param string $section
     * @return \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    public function log($message, $section = null)
    {   
        $section = $section ? '.'.$section : '';
        $message = self::MESSAGE_PREFIX . $section .': '. $message;
        $this->logger->log(self::LOG_LEVEL, $message);
        return $this;
    }

}
