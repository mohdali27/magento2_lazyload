<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock;

/**
 * Class AdjustStock
 * @package Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock
 */
abstract class AdjustStock extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    
    /**
     * @var \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface
     */
    protected $adjustStockManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\AdjustStockFactory
     */
    protected $adjustStockFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock
     */
    protected $adjustStockResource;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface
     */
    protected $stockChange;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $adminSession;

    /**
     * @var \Magestore\InventorySuccess\Api\Helper\SystemInterface
     */
    protected $systemHelper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\Filesystem\File\WriteFactory
     */
    protected $fileWriteFactory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    /**
     * Helper Data
     *
     * @var \Magestore\InventorySuccess\Helper\Data
     */
    protected $helper;


    /**
     * AdjustStock constructor.
     * @param \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper
     * @param \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock $adjustStockResource
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magestore\InventorySuccess\Helper\Data $helper
     */
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper,
        \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement,
        \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock $adjustStockResource,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magestore\InventorySuccess\Helper\Data $helper
    ){
        parent::__construct($context);
        $this->moduleManager = $moduleManager;
        $this->systemHelper = $systemHelper;
        $this->adjustStockManagement = $adjustStockManagement;
        $this->adjustStockFactory = $adjustStockFactory;
        $this->adjustStockResource = $adjustStockResource;
        $this->warehouseFactory = $context->getWarehouseFactory();
        $this->stockChange = $stockChange;
        $this->adminSession = $adminSession;
        $this->filesystem = $filesystem;
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->fileWriteFactory = $fileWriteFactory;
        $this->driverFile = $driverFile;
        $this->helper = $helper;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_InventorySuccess::adjuststock');
    }
}