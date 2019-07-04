<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\External\Form\Modifier;

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
class ExternalStock extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
{
    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $warehouseSource;

    /**
     * @var TransferStockModel\TransferStockManagement
     */
    protected $_transferStockManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * PermissionManagementInterface
     *
     * @var \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface
     */
    protected $_permissionManagement;

    /**
     * @var WarehouseFactory
     */
    protected $_warehouseFactory;

    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $collectionFactory,
        WarehouseFactory $warehouseFactory,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagementInterface,

        array $_modifierConfig = []
    ) {
        parent::__construct($urlBuilder, $request, $_modifierConfig);
        $this->_warehouseSource = $warehouseSource;
        $this->_transferStockManagement = $transferStockManagement;
        $this->collectionFactory = $collectionFactory;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_coreRegistry = $registry;
        $this->_permissionManagement = $permissionManagementInterface;
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
        if($transferStock){
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
    public function getCollapsible(){
        if ($this->getTransferStockStatus() != TransferStockModel::STATUS_COMPLETED)
            return $this->_collapsible;
        return false;
    }

    /**
     * get group label
     *
     * @param
     * @return boolean
     */
    public function getGroupLabel(){
        if ($this->getTransferStockStatus() != TransferStockModel::STATUS_COMPLETED)
            return $this->_groupLabel;
        return '';
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
        switch ($type){
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

    public function getElementTmpl($type, $canEditLater){
        $result = static::TMPL_TEXT_LABEL;
        if (!$this->getTransferStockStatus()) {
            switch ($type){
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
        }
        else{
            if($this->getTransferStockStatus() != TransferStockInterface::STATUS_PENDING){
                $canEditLater = false;
            }

            if($canEditLater){
                switch ($type){
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
            }
            else{
                switch ($type){
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
    protected  function getModalListing()
    {
        $data =  parent::getModalListing();
        $data['arguments']['data']['config']['imports']['warehouse_label_id'] = '${ $.provider }:data.warehouse_label_id';
        $data['arguments']['data']['config']['exports']['warehouse_label_id'] = '${ $.externalProvider }:params.warehouse_label_id';
        return $data;
    }
}
