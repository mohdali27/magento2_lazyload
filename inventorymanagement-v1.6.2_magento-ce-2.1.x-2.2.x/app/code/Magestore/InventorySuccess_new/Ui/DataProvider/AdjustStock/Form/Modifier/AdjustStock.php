<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\AdjustStock\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdjustStock extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock\Product\CollectionFactory
     */
    protected $collection;

    /**
     * @var \Magestore\InventorySuccess\Model\AdjustStockFactory
     */
    protected $adjustStockFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface
     */
    protected $adjustStockManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock
     */
    protected $adjustStockResource;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $warehouseSource;

    /**
     * @var \Magestore\InventorySuccess\Model\AdjustStock\Options\Status
     */
    protected $adjustStockStatus;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Helper Data
     *
     * @var \Magestore\InventorySuccess\Helper\Data
     */
    protected $helper;

    /**
     * Generate constructor.
     * @param \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock\Product\CollectionFactory $collectionFactory
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement
     * @param \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock $adjustStockResource
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource
     * @param \Magestore\InventorySuccess\Model\AdjustStock\Options\Status $adjustStockStatus
     * @param \Magestore\InventorySuccess\Helper\Data $helper
     * @param array $_modifierConfig
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock\Product\CollectionFactory $collectionFactory,
        \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory,
        \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement,
        \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock $adjustStockResource,
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $coreRegistry,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        \Magestore\InventorySuccess\Model\AdjustStock\Options\Status $adjustStockStatus,
        \Magestore\InventorySuccess\Helper\Data $helper,
        array $_modifierConfig = []
    ) {
        parent::__construct($urlBuilder, $request, $_modifierConfig);
        $this->collection = $collectionFactory->create();
        $this->adjustStockFactory = $adjustStockFactory;
        $this->adjustStockManagement = $adjustStockManagement;
        $this->adjustStockResource = $adjustStockResource;
        $this->adjustStockStatus = $adjustStockStatus;
        $this->warehouseSource = $warehouseSource;
        $this->urlBuilder = $urlBuilder;
        $this->coreRegistry = $coreRegistry;
        $this->helper = $helper;
    }

    /**
     * Get current Adjustment
     *
     * @return Adjustment
     * @throws NoSuchEntityException
     */
    public function getCurrentAdjustment()
    {
        $adjustStock = $this->coreRegistry->registry('current_adjuststock');
        return $adjustStock;
    }

    /**
     * Get adjust stock status
     *
     * @return string
     */
    public function getAdjustStockStatus()
    {
        $adjustStock = $this->getCurrentAdjustment();
        if($adjustStock->getId()){
            return $adjustStock->getData('status');
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
        if ($this->getAdjustStockStatus() != '1')
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
        if ($this->getAdjustStockStatus() != '1')
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
        if ($this->getAdjustStockStatus() == AdjustStockInterface::STATUS_COMPLETED) {
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

        if ($this->getAdjustStockStatus() != null) {
            if ($type == 'select'){
                return static::TMPL_SELECT_LABEL;
            }
        }
        return parent::getModifyTmpl($type);
    }
}
