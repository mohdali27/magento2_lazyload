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
namespace Velanapps\PackingSlips\Controller\Adminhtml\Sales\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Velanapps\PackingSlips\Model\Sales\Order\Pdf\PackingSlip;
use Velanapps\PackingSlips\Helper\Data as PackingSlipsHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order as OrderFactory;

class PdfPackingslip extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $redirectUrl = 'sales/*/';
	
	/**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;
	
	/**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var PackingSlip
     */
    protected $pdfPackingSlip;
	
	/**
     * @var PackingSlipsHelper
     */
    protected $packingSlipsHelper;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param PackingSlip $packingSlip
     * @param PackingSlipsHelper $packingSlipsHelper
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        DateTime $dateTime,
        FileFactory $fileFactory,
        PackingSlip $packingSlip,
		PackingSlipsHelper $packingSlipsHelper
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfPackingSlip = $packingSlip;
        $this->packingSlipsHelper = $packingSlipsHelper;
		$this->orderFactory = $orderFactory;
        parent::__construct($context);
    }
	
	/**
     * Print packingslips for selected order
     *
     * @param OrderFactory $order
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     */
    protected function printAction(OrderFactory $order)
    {
		if (!$order->getId()) {
            $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }
		
		$pdf = $this->pdfPackingSlip->getPdf([$order]);
		
        return $this->fileFactory->create(
			sprintf('packingslip%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
			$pdf->render(),
			DirectoryList::VAR_DIR,
			'application/pdf'
		);
    }
	
	/**
     * @return ResponseInterface|void
     */
    public function execute()
    {
		try {
			$orderId = $this->getRequest()->getParam('order_id');
			if ($orderId) {
				$order = $this->orderFactory->load($orderId);
				if($this->packingSlipsHelper->isEnabled($order->getStoreId())) {
					if ($order) {
						return $this->printAction($order);
					} else {
						$this->messageManager->addError(__('There are no printable documents related to selected orders.'));
					}
				} else {
					$this->messageManager->addError(__('Sorry, print pre-packing slip status is disabled.'));
				}
			} else {
				$this->messageManager->addError(__('There are no printable documents related to selected orders.'));
			}
		}  catch (\Exception $e) {			
            $this->messageManager->addError($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }
	
	/**
     * Return component referer url
     * TODO: Technical dept referer url should be implement as a part of Action configuration in in appropriate way
     *
     * @return null|string
     */
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl()?: 'sales/order/*';
    }
}