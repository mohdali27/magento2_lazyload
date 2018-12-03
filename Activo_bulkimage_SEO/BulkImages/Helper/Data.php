<?php
/**
 * Activo Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Activo Commercial License
 * that is available through the world-wide-web at this URL:
 * http://extensions.activo.com/license_professional
 *
 * @copyright   Copyright (c) 2017 Activo Extensions (http://extensions.activo.com)
 * @license     Commercial
 */
namespace Activo\BulkImages\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * getStoreConfig
     * Get system configration value for specific group
     * @param type $group
     * @return type string
     */
    public function getStoreConfig($group)
    {
        $strore_config_val = $this->scopeConfig->getValue($group, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $strore_config_val;
    }
}
