<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Stocktaking
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
abstract class Stocktaking extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface
     */
    protected $stocktakingManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\StocktakingFactory
     */
    protected $stocktakingFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking
     */
    protected $stocktakingResource;

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
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Stocktaking constructor.
     * @param \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper
     * @param \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface $stocktakingManagement
     * @param \Magestore\InventorySuccess\Model\StocktakingFactory $stocktakingFactory
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking $stocktakingResource
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper,
        \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface $stocktakingManagement,
        \Magestore\InventorySuccess\Model\StocktakingFactory $stocktakingFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking $stocktakingResource,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csvProcessor,
        TimezoneInterface $timezone
    ){
        parent::__construct($context);
        $this->moduleManager = $moduleManager;
        $this->systemHelper = $systemHelper;
        $this->stocktakingManagement = $stocktakingManagement;
        $this->stocktakingFactory = $stocktakingFactory;
        $this->stocktakingResource = $stocktakingResource;
        $this->warehouseFactory = $context->getWarehouseFactory();
        $this->stockChange = $stockChange;
        $this->adminSession = $adminSession;
        $this->filesystem = $filesystem;
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->timezone = $timezone;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_InventorySuccess::stocktaking');
    }
}