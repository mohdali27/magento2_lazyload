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
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Velanapps\PackingSlips\Model\Sales\Order\Pdf\PackingSlip;
use Velanapps\PackingSlips\Helper\Data as PackingSlipsHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassPdfPackingslip extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
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
        Filter $filter,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        FileFactory $fileFactory,
        PackingSlip $packingSlip,
		PackingSlipsHelper $packingSlipsHelper
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfPackingSlip = $packingSlip;		
		$this->packingSlipsHelper = $packingSlipsHelper;
		$this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }

    /**
     * Print packingslips for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
		if(!$this->packingSlipsHelper->isEnabled()) {
			$this->messageManager->addError(__('Sorry, print pre-packing slip status is disabled'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
		}	
		
		if (!$collection->getSize()) {
            $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }
        return $this->fileFactory->create(
            sprintf('packingslip%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $this->pdfPackingSlip->getPdf($collection->getItems())->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}