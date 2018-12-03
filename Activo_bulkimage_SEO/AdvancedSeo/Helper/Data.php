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
namespace Activo\AdvancedSeo\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_scopeConfig;

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    public function getBrandAttribute()
    {

        if (!$this->_scopeConfig->getValue('advancedseo/brand/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return;
        }

        return $this->_scopeConfig->getValue('advancedseo/brand/attribute_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
