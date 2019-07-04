<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_InventorySuccess
 * @module   Inventorysuccess
 * @author   Magestore Developer
 */
/**
 * Class AbstractAction
 * @package Magestore\InventorySuccess\Controller\Adminhtml
 */
abstract class AbstractAction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $_resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $_logger;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStockFactory
     */
    protected $_transferStockFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagementFactory
     */
    protected $_transferStockManagementFactory;

    /**
     * AbstractAction constructor.
     *
     * @param Context $context
     */
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->_eventManager = $context->getEventManager();
        $this->_coreRegistry = $context->getCoreRegistry();
        $this->_resultForwardFactory = $context->getResultForwardFactory();
        $this->_resultPageFactory = $context->getResultPageFactory();
        $this->_resultLayoutFactory = $context->getResultLayoutFactory();
        $this->_logger = $context->getLogger();
        $this->_warehouseFactory = $context->getWarehouseFactory();
        $this->_transferStockFactory = $context->getTransferStockFactory();
        $this->_transferStockManagementFactory = $context->getTransferStockManagementFactory();
    }
}