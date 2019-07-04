<?php
/**
 * Solwin Infotech
 * Solwin Ournews Extension
 *
 * @category   Solwin
 * @package    Solwin_Ournews
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
?>
<?php

namespace Solwin\Ournews\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *  \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
     
    public function __construct(
        \Magento\Framework\App\Helper\Context $context, 
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * get enable/disable extension
     */
    public function getEnableModule() {
        return $this->scopeConfig->getValue('newssection/newsgroup/active',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * get base url with store code
     */
    public function getBaseUrlWithStoreCode() {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    /**
     * Return news config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key)
    {
        $result = $this->scopeConfig
                ->getValue(
                        $key, 
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        );
        return $result;
    }
}
