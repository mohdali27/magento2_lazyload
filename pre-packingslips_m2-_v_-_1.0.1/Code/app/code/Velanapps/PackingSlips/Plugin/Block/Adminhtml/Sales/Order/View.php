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
namespace Velanapps\PackingSlips\Plugin\Block\Adminhtml\Sales\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View
{
    public function beforeSetLayout(OrderView $subject)
    {
		$order = $subject->getOrder();
		
		if($order->getId()) {
		
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$packingSlipsHelper = $objectManager->get('Velanapps\PackingSlips\Helper\Data');
			
			if($packingSlipsHelper->isEnabled($order->getStoreId())) {
				/* Add Button */
				$subject->addButton(
					'pdfprepackingslip_order',
					[
						'label' => __('Print Pre-Packing Slip'),
						'onclick' => 'setLocation(\'' . $subject->getUrl('packingslips/sales_order/pdfPackingslip') . '\')'
					]
				);
			}
		}
    }
}