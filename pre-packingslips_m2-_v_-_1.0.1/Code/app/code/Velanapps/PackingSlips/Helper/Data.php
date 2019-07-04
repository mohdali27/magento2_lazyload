<?php
/*
 * Velan Info Services India Pvt Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://store.velanapps.com/License.txt
 *
 /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
 /* This package designed for Magento COMMUNITY edition
 * Velan Info Services does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Velan Info Services does not provide extension support in case of
 * incorrect edition usage.
 /***************************************
 *         DISCLAIMER   *
 * *************************************** */
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category            velanapps
 * @package             Pre PackingSlips
 * @author              Velan Team 
 * @supported versions  Magento 2.1.x - Magento 2.2.x
 * @copyright           Copyright (c) 2018 Velan Info Services India Pvt Ltd. (https://www.velanapps.com)
 * @license             https://store.velanapps.com/License.txt
 */
namespace Velanapps\PackingSlips\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity;

class Data extends AbstractHelper
{
	const XML_PATH_PACKINGSLIP_ENABLED = 'packingslips/settings/active';
	const XML_PATH_PACKINGSLIP_ACTIVATION_KEY = 'packingslips_activation/activation/key';
	const XML_PATH_PACKINGSLIP_ACTIVATION_KEY_CONFIRM = 'packingslips_activation/activation/key_confirm';
	
	/**
     * Get store config value
     *
     * @param config path $path
     * @param int $storeId
     * @return string|int
     */
	public function getConfigValue($path, $storeId = null)
	{
		return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
	}
	
	/**
     * Get domain url
     *
     * @param string $url
     * @return string
     */
	public function getDomain($url)
	{
		$domain = @parse_url($url, PHP_URL_HOST);
		
		if(!$domain) $domain = $url;
		
		if(substr($domain, 0, 4) == 'www.') 
			$domain = substr($domain, 4);
		
		return $domain;
	}
	
	/**
     * Check if module enable or disable status
     *
     * @param int $storeId
     * @return bool
     */
	public function isEnabled($storeId = null)
	{
		return ($this->getConfigValue(self::XML_PATH_PACKINGSLIP_ENABLED, $storeId) && $this->validateActivationKey());
	}	
	
	/**
     * Check if order mail
     *
     * @param int|string $templateId
     * @param int $storeId
     * @return bool
     */
	public function isOrderMail($templateId, $storeId = null)
	{
		return (($templateId == $this->getConfigValue(OrderIdentity::XML_PATH_EMAIL_TEMPLATE, $storeId))
			||  ($templateId == $this->getConfigValue(OrderIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId)));
	}
	
	/**
     * Check if order comment mail
     *
     * @param int|string $templateId
     * @param int $storeId
     * @return bool
     */
	public function isOrderCommentMail($templateId, $storeId = null)
	{
		return (($templateId == $this->getConfigValue(OrderCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId)) 
			|| ($templateId == $this->getConfigValue(OrderCommentIdentity::XML_PATH_EMAIL_TEMPLATE, $storeId)));
	}
	
	/**
     * Get attach packing slip availability
     *
     * @param int|string $templateId
     * @param int $storeId
     * @return bool
     */
	public function canAttachPackingSlipPdf($groupId, $storeId)
	{
		return $this->getConfigValue('sales_email/' . $groupId . '/attachpackingslipaspdf', $storeId);
	}
	
	/**
     * Get activation key
     *
     * @return string
     */
	public function getActivationKey()
	{
		return $this->getConfigValue(self::XML_PATH_PACKINGSLIP_ACTIVATION_KEY);
	}
	
	/**
     * Get activation key confirm
     *
     * @return string
     */
	public function getActivationKeyConfirm()
	{
		return $this->getConfigValue(self::XML_PATH_PACKINGSLIP_ACTIVATION_KEY_CONFIRM);
	}
	
	/**
     * validate activation key
     *
     * @return bool
     */
	public function validateActivationKey()
	{
		$activationKey = $this->getActivationKey();
		$activationKeyConfirm = $this->getActivationKeyConfirm();
		
		return $this->verifyActivationKey($activationKey, $activationKeyConfirm);
	}
	
	/**
     * verify activation key
     *
	 * @param string $key
	 * @param string $keyConfirm
     * @return bool
     */
	public function verifyActivationKey($key, $keyConfirm)
	{
		return (($key && $keyConfirm) && (base64_encode($key) == $keyConfirm));
	}
	
	/**
     * If access from localhost
     *	 
     * @return bool
     */
	public function isLocalhost()
	{
        return in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'));
    }
}	
