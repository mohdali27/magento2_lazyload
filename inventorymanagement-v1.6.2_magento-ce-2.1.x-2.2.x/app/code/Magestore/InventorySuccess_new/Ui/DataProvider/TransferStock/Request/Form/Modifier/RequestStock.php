<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Request\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Model\TransferStock as TransferStockModel;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;
use Magestore\InventorySuccess\Model\WarehouseFactory;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequestStock extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\Collection
     */
    protected $collection;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStockFactory
     */
    protected $_transferStockFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\TransferStock
     */
    protected $_transferStockResource;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $_warehouseSource;

    /** @var  \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement */
    protected $_transferStockManagement;

    /** @var  \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagementFactory */
    protected $_transferActivityManagementFactory;

    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory */
    protected $_collectionFactory;

    /**
     * PermissionManagementInterface
     *
     * @var \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface
     */
    protected $_permissionManagement;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @var WarehouseFactory
     */
    protected $_warehouseFactory;


    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock $transferStockResource,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $collectionFactory,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory,
        \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagementFactory $transferActivityManagementFactory,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagementInterface,
        WarehouseFactory $warehouseFactory,
        array $_modifierConfig = []
    )
    {
        parent::__construct($urlBuilder, $request, $_modifierConfig);
        $this->collection = $collectionFactory->create();
        $this->_transferStockFactory = $transferStockFactory;
        $this->_transferStockResource = $transferStockResource;
        $this->_warehouseSource = $warehouseSource;
        $this->urlBuilder = $urlBuilder;
        $this->_transferStockManagement = $transferStockManagement;
        $this->_coreRegistry = $registry;
        $this->_transferActivityManagementFactory = $transferActivityManagementFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_permissionManagement = $permissionManagementInterface;
        $this->_warehouseFactory = $warehouseFactory;
    }

    /**
     * Get current transfer stock
     *
     * @return \Magestore\InventorySuccess\Model\TransferStock
     */
    public function getCurrentTransferStock()
    {
        return $this->_coreRegistry->registry('current_transferstock');
    }

    /**
     * Get adjust stock status
     *
     * @return string
     */
    public function getTransferStockStatus()
    {
        $transferStock = $this->getCurrentTransferStock();
        if ($transferStock) {
            return $transferStock->getStatus();
        }
        return null;
    }

    /**
     * is disabled element
     *
     * @param
     * @return
     */
    public function isDisabledElement()
    {
        if ($this->request->getParam('id'))
            return 'disabled';
        return false;
    }

    /**
     * get collapsible
     *
     * @param
     * @return boolean
     */
    public function getCollapsible()
    {
        return $this->_collapsible;
    }

    /**
     * get group label
     *
     * @param
     * @return boolean
     */
    public function getGroupLabel()
    {
        return $this->_groupLabel;
    }

    /**
     * get modify tmpl
     *
     * @param
     * @return
     */
    public function getModifyTmpl($type)
    {
        if ($this->getTransferStockStatus() == TransferStockModel::STATUS_COMPLETED) {
            return static::TMPL_TEXT_LABEL;
        }
        switch ($type) {
            case 'input':
                return static::TMPL_TEXT_LABEL;
                break;
            case 'textarea':
                return static::TMPL_TEXTAREA_LABEL;
                break;
            case 'select':
                return static::TMPL_SELECT_LABEL;
                break;
            default:
                return static::TMPL_TEXT_LABEL;
        }
    }

    public function getElementTmpl($type, $canEditLater)
    {
        $result = static::TMPL_TEXT_LABEL;
        if (!$this->getTransferStockStatus()) {
            switch ($type) {
                case 'input':
                    $result = static::TMPL_INPUT;
                    break;
                case 'textarea':
                    $result = static::TMPL_TEXTAREA;
                    break;
                case 'select':
                    $result = static::TMPL_SELECT;
                    break;
                default:
                    $result = static::TMPL_INPUT;
            }
        } else {
            if ($this->getTransferStockStatus() != TransferStockInterface::STATUS_PENDING) {
                $canEditLater = false;
            }

            if ($canEditLater) {
                switch ($type) {
                    case 'input':
                        $result = static::TMPL_INPUT;
                        break;
                    case 'textarea':
                        $result = static::TMPL_TEXTAREA;
                        break;
                    case 'select':
                        $result = static::TMPL_SELECT;
                        break;
                    default:
                        $result = static::TMPL_INPUT;
                }
            } else {
                switch ($type) {
                    case 'input':
                        $result = static::TMPL_TEXT_LABEL;
                        break;
                    case 'textarea':
                        $result = static::TMPL_TEXTAREA_LABEL;
                        break;
                    case 'select':
                        $result = static::TMPL_SELECT_LABEL;
                        break;
                    default:
                        $result = static::TMPL_TEXT_LABEL;
                }
            }
        }
        return $result;
    }   
}
