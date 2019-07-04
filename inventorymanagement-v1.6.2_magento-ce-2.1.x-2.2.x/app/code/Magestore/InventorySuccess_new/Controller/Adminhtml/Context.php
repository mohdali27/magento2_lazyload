<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml;

use Magento\Framework\Controller\ResultFactory as ResultFactory;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class Context extends \Magento\Backend\App\Action\Context
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
     * @var \Magento\CatalogInventory\Model\Configuration
     */
    protected $_catalogInventoryConfiguration;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $_logger;

//
    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStockFactory
     */
    protected $_transferStockFactory;

    /**
     * @var  \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagementFactory;
     */

    protected $_transferStockManagementFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory
     */
    protected $transferActivityFactory;

    /**
     * Context constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\App\ViewInterface $view
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param ResultFactory $resultFactory
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Helper\Data $helper
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Logger\Monolog $logger
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory
     * @param \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory $transferActivityFactory
     * @param \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagementFactory $transferStockManagementFactory
     * @param bool $canUseBaseUrl
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        ResultFactory $resultFactory,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Helper\Data $helper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Logger\Monolog $logger,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory $transferActivityFactory,
        \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagementFactory $transferStockManagementFactory,
        $canUseBaseUrl = false
    ) {
        parent::__construct(
            $request,
            $response,
            $objectManager,
            $eventManager,
            $url,
            $redirect,
            $actionFlag,
            $view,
            $messageManager,
            $resultRedirectFactory,
            $resultFactory,
            $session,
            $authorization,
            $auth,
            $helper,
            $backendUrl,
            $formKeyValidator,
            $localeResolver,
            $canUseBaseUrl
        );

        $this->_eventManager = $eventManager;
        $this->_coreRegistry = $registry;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_logger = $logger;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_transferStockFactory = $transferStockFactory;
        $this->_transferStockManagementFactory = $transferStockManagementFactory;
        $this->transferActivityFactory = $transferActivityFactory;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getCoreRegistry()
    {
        return $this->_coreRegistry;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\ForwardFactory
     */
    public function getResultForwardFactory()
    {
        return $this->_resultForwardFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function getResultPageFactory()
    {
        return $this->_resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\LayoutFactory
     */
    public function getResultLayoutFactory()
    {
        return $this->_resultLayoutFactory;
    }

    /**
     * @return \Magento\Framework\Logger\Monolog
     */
    public function getLogger()
    {
        return $this->_logger;
    }
    /**
     * @return \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    public function getWarehouseFactory()
{
    return $this->_warehouseFactory;
}

    /**
     * @return \Magestore\InventorySuccess\Model\TransferStockFactory
     */
    public function getTransferStockFactory(){
        return $this->_transferStockFactory;
    }
    
    /**
     * @return \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagementFactory
     */
    public function getTransferStockManagementFactory(){
        return $this->_transferStockManagementFactory;
    }
    
    public function getTransferActivityFactory(){
        return $this->transferActivityFactory;
    }
}