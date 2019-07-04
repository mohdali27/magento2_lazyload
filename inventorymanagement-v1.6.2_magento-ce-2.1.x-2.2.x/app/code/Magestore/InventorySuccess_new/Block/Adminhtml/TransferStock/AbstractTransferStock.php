<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\TransferStock;


class AbstractTransferStock extends \Magento\Backend\Block\Template
{
    /** @var \Magestore\InventorySuccess\Model\TransferStockFactory $_transferStockFactory */
    protected $_transferStockFactory;

    /**
     * PermissionManagementInterface
     *
     * @var \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface
     */
    protected $_permissionManagement;
    
    /**
     * @var \Magento\Framework\Registry 
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagementInterface,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_withProductCount = true;
        $this->_transferStockFactory = $transferStockFactory;
        $this->_permissionManagement = $permissionManagementInterface;
        parent::__construct($context, $data);
    }
    
    /**
     * Get current Warehouse which creating adjust
     * 
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getWarehouse()
    {
        return $this->_coreRegistry->registry('current_warehouse');
    }    
}
