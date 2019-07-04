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
namespace Velanapps\PackingSlips\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\App\Config\ScopeConfigInterface;
 
class ModuleActivation implements ObserverInterface
{
	/**
     * @var ManagerInterface
     */
	protected $_messageManager;
	
	/**
     * @var Data
     */
	protected $_packingSlipsHelper;
	
	/**
     * @var ResponseFactory
     */
	protected $_responseFactory;
	
	/**
     * @var UrlInterface
     */
	protected $_urlInterface;
	
	/**
     * @var StoreManagerInterface
     */
	protected $_storeManager;
	
	/**
     * @var WriterInterface
     */
	protected $_configWriter;
	
	
	/**
	 * @param \Magento\Framework\Message\ManagerInterface $messageManager
	 * @param \Magento\Framework\App\ResponseFactory $responseFactory
	 * @param \Velanapps\PackingSlips\Helper\Data $packingSlipsHelper
	 * @param \Magento\Framework\UrlInterface $urlInterface
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Framework\App\Config\Storage\WriterInterface $WriterInterface
	 * @param \Magento\Framework\App\Cache\Manager $cacheManager
	 */
	public function __construct(
		\Magento\Framework\Message\ManagerInterface $messageManager, 
		\Magento\Framework\App\ResponseFactory $responseFactory,
		\Velanapps\PackingSlips\Helper\Data $packingSlipsHelper,
		\Magento\Framework\UrlInterface $urlInterface,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
		\Magento\Framework\App\Cache\Manager $cacheManager
	) {
        $this->_messageManager = $messageManager;
        $this->_packingSlipsHelper = $packingSlipsHelper;
        $this->_responseFactory = $responseFactory; 
		$this->_urlInterface = $urlInterface;
        $this->_storeManager = $storeManager;	
        $this->_configWriter = $configWriter;
		$this->_cacheManager = $cacheManager;	
	}

	/**
	 * Validates Activation Key
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void     
     */ 
    public function execute(\Magento\Framework\Event\Observer $observer)
	{
		if($this->_packingSlipsHelper->isLocalhost()) return;
		
		if($this->_packingSlipsHelper->validateActivationKey()) {
			$this->_messageManager->addSuccess(__('Velanapps Packingslips - Product is activated!'));
			return;
		}
		
		$activationCode = $this->_packingSlipsHelper->getActivationKey();
        
		try {
			if($activationCode) {
				$store = $this->_storeManager->getStore();
				
				$domainName    = $this->_packingSlipsHelper->getDomain($store->getBaseUrl());
				$serviceUrl    = base64_decode('aHR0cHM6Ly9zdG9yZS52ZWxhbmFwcHMuY29tL2FjdGl2YXRpb24vcmVnaXN0ZXIvcGFja2luZ1NsaXBzTTJBcGk=');
				$curlPostData  = array('activation_key' => $activationCode, 'domain_name' => $domainName);
				
				$ch = curl_init($serviceUrl);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPostData);
				$response = curl_exec($ch);						                                   
				curl_close($ch);
				
				$result = json_decode($response, true);
				
				if($result && ($result['status'] == false)) {
					throw new ValidatorException(__('Activation key is invalid!'));
				} elseif(($result['status'] == true) && isset($result['key_confirm'])) {
					$feed = file_get_contents('/srv/public_html/app/code/Velanapps/PackingSlips/etc/adminhtml/system.xml');
					$xmlDocument = simplexml_load_string($feed);
					//$xmlDocument = simplexml_load_file($result['xmlFilePath']);
					
					foreach($result['sections'] as $secId => $attrValues) {
						foreach($attrValues as $attrKey => $attrValue) {
							if(isset($xmlDocument->system->section[$secId][$attrKey])) {
								$xmlDocument->system->section[$secId][$attrKey] = $attrValue;
							}
						}
					}
					
					$xmlDocument->asXml('/srv/public_html/app/code/Velanapps/PackingSlips/etc/adminhtml/system.xml'); 
					$this->_configWriter->save($result['key_confirm_path'],  $result['key_confirm'], ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);	
					$this->_cacheManager->clean(['config']);
					
					$this->_messageManager->addSuccess(__('Velanapps Packingslips - Product is activated!'));
					
					$redirectUrl = $this->_urlInterface->getUrl('*/*/edit', array('section' => $result['selSection']));
					$this->_responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
					die;
					
				} else {
					if(isset($result['message']) && !empty($result['message'])) {
						throw new ValidatorException(__('API Error : %s', $result['message']));
					}
				}
        	} else {
				throw new ValidatorException(__('Please enter your activation key to complete the registration process.'));
			}
        } catch(\Exception $e) {
			$this->_messageManager->addError(__($e->getMessage()));
        }
    }
}