<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\LowStockNotification;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\CollectionFactory
     */
    protected $_notificationCollectionFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    public function __construct(
        Context $context,
        \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Product\CollectionFactory $productCollectionFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\CollectionFactory $notificationCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_notificationCollectionFactory = $notificationCollectionFactory;
        $this->filesystem = $filesystem;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $notificationId = $this->getRequest()->getParam('notification_id');
        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Notification $notification */
        $notification = $this->_notificationCollectionFactory->create()
            ->addFieldToFilter('notification_id', $notificationId)
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
        $heading = [];
        if ($notification->getData('lowstock_threshold_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY) {
            $heading = [
                __('Id'),
                __('SKU'),
                __('Name'),
                __('Current Qty'),
                __('Qty. Sold/day'),
                __('Total Sold'),
                __('Availability Days'),
                __('Availability Date')
            ];
        }

        if ($notification->getData('lowstock_threshold_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY) {
            $heading = [
                __('Id'),
                __('SKU'),
                __('Name'),
                __('Current Qty')
            ];
        }
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Product\Collection $productCollection */
        $productCollection = $this->_productCollectionFactory->create()
            ->addFieldToFilter('notification_id', $notificationId);
        $outputFile = "LowStockNotification_". date('Ymd_His').".csv";
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('download');
        $filename = DirectoryList::VAR_DIR.'/download/'.$outputFile;

        $stream = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->openFile($filename, 'w+');
        $stream->lock();
        $stream->writeCsv($heading);
        foreach ($productCollection as $product) {
            if ($notification->getData('lowstock_threshold_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY) {
                $row = [
                    $product->getProductId(),
                    $product->getProductSku(),
                    $product->getProductName(),
                    $product->getCurrentQty(),
                    $product->getSoldPerDay(),
                    $product->getTotalSold(),
                    $product->getAvailabilityDays(),
                    $product->getAvailabilityDate()
                ];
            }
            if ($notification->getData('lowstock_threshold_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY) {
                $row = [
                    $product->getProductId(),
                    $product->getProductSku(),
                    $product->getProductName(),
                    $product->getCurrentQty()
                ];
            }
            $stream->writeCsv($row);
        }
        $stream->unlock();
        $stream->close();

        return $this->fileFactory->create(
            $outputFile,
            [
                'type' => 'filename',
                'value' => $filename,
                'rm' => true  // can delete file after use
            ],
            DirectoryList::VAR_DIR
        );
    }
}
