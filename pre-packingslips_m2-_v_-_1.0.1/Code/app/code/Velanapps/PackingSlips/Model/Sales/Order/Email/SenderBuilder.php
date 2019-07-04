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
namespace Velanapps\PackingSlips\Model\Sales\Order\Email;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
	const XML_PATH_ATTACH_PDF = 'sales_email/order/attachpdf';
	
	/**
     * Get ObjectManager
     *
     * @return object
     */
	protected function _getObjectManager() {
		return \Magento\Framework\App\ObjectManager::getInstance();
	}
	
	/**
     * Get helper
     *
     * @return object
     */
	protected function _getPackingSlipsHelper() {
		return $this->_getObjectManager()->get('Velanapps\PackingSlips\Helper\Data');
	}
	
	/**
     * Can allow to attach packing slip pdf
     *
     * @return bool
     */
	protected function canAttachPackingSlipPdf($templateId, $orderStoreId) {
		$packingSlipsHelper = $this->_getPackingSlipsHelper();
		
		/* Check if order mail */
		if($packingSlipsHelper->isOrderMail($templateId, $orderStoreId)) {
			return $packingSlipsHelper->canAttachPackingSlipPdf('order', $orderStoreId);
		}
		
		/* Check if order comment mail */
		if($packingSlipsHelper->isOrderCommentMail($templateId, $orderStoreId)) {
			return $packingSlipsHelper->canAttachPackingSlipPdf('order_comment', $orderStoreId);
		}
		
		return false;
	}
    
	/**
     * Check and attach packing slip pdf     
	 *
     * @return void
     */
	protected function checkAndAttachPackingSlipPdf() 
	{
		$templateVars = $this->templateContainer->getTemplateVars();
		
		if(isset($templateVars['order']) && !empty($templateVars['order']) && ($templateVars['order'] instanceof \Magento\Sales\Model\Order)) {
			$order = $templateVars['order'];
			
			$packingSlipsHelper = $this->_getPackingSlipsHelper();
			$orderStoreId = $order->getStoreId();
			
			if(($order->getId() && $packingSlipsHelper->isEnabled($orderStoreId))) {
				if ($order->getCustomerIsGuest()) {
					$templateId = $this->identityContainer->getGuestTemplateId();
				} else {
					$templateId = $this->identityContainer->getTemplateId();
				}
				
				if($this->canAttachPackingSlipPdf($templateId, $orderStoreId)) {
					$pdf = $this->_getObjectManager()->create('Velanapps\PackingSlips\Model\Sales\Order\Pdf\PackingSlip')->getPdf([$order]);
					$this->transportBuilder->addPdfAttachment($pdf->render(), 'PackingSlip.pdf');
				}
			}
		}
	}
	
	/**
     * Prepare and send email message
     *
     * @return void
     */
	public function send()
    {	
		$this->checkAndAttachPackingSlipPdf();
        parent::send();
    }

	/**
     * Prepare and send copy email message
     *
     * @return void
     */
    public function sendCopyTo()
    {
		$this->checkAndAttachPackingSlipPdf();
        parent::sendCopyTo();
    }
}