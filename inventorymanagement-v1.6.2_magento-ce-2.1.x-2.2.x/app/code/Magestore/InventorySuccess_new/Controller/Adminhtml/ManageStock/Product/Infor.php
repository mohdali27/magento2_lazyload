<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\ManageStock\Product;

use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Grid
 * @package Magestore\InventorySuccess\Controller\Adminhtml\ManageStock\Product
 */
class Infor extends \Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\AbstractWarehouse
{
    protected $_stockGrid = 'Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Infor';
    protected $_stockGridName = 'warehouse.product.infor';
    
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Grid constructor.
     * @param \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ){
        parent::__construct($context, $permissionManagement, $dataPersistor);
        $this->dataPersistor = $dataPersistor;
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                $this->_stockGrid,
                $this->_stockGridName
            )->toHtml()
        );
    }
}