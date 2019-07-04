<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\Stocktaking\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Model\Stocktaking as StocktakingModel;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Stocktaking extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Product\CollectionFactory
     */
    protected $collection;

    /**
     * @var \Magestore\InventorySuccess\Model\StocktakingFactory
     */
    protected $stocktakingFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface
     */
    protected $stocktakingManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking
     */
    protected $stocktakingResource;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $warehouseSource;

    /**
     * @var \Magestore\InventorySuccess\Model\Stocktaking\Options\Status
     */
    protected $stocktakingStatus;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;


    /**
     * @var  \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected  $date;

    /**
     * Generate constructor.
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Product\CollectionFactory $collectionFactory
     * @param \Magestore\InventorySuccess\Model\StocktakingFactory $stocktakingFactory
     * @param \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface $stocktakingManagement
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking $stocktakingResource
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource
     * @param \Magestore\InventorySuccess\Model\Stocktaking\Options\Status $stocktakingStatus
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param array $_modifierConfig
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Product\CollectionFactory $collectionFactory,
        \Magestore\InventorySuccess\Model\StocktakingFactory $stocktakingFactory,
        \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface $stocktakingManagement,
        \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking $stocktakingResource,
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        \Magestore\InventorySuccess\Model\Stocktaking\Options\Status $stocktakingStatus,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $_modifierConfig = []
    ) {
        parent::__construct($urlBuilder, $request, $_modifierConfig);
        $this->collection = $collectionFactory->create();
        $this->stocktakingFactory = $stocktakingFactory;
        $this->stocktakingManagement = $stocktakingManagement;
        $this->stocktakingResource = $stocktakingResource;
        $this->stocktakingStatus = $stocktakingStatus;
        $this->warehouseSource = $warehouseSource;
        $this->urlBuilder = $urlBuilder;
        $this->coreRegistry = $coreRegistry;
        $this->date = $date;
    }

    /**
     * Get current Stocktaking
     *
     * @return Stocktaking
     * @throws NoSuchEntityException
     */
    public function getCurrentStocktaking()
    {
        $stocktaking = $this->coreRegistry->registry('current_stocktaking');
        return $stocktaking;
    }

    /**
     * Get stocktaking status
     *
     * @return string
     */
    public function getStocktakingStatus()
    {
        $stocktaking = $this->getCurrentStocktaking();
        if($stocktaking->getId()){
            return $stocktaking->getData('status');
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
        if ($this->getStocktakingStatus() != StocktakingModel::STATUS_COMPLETED)
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
        if ($this->getStocktakingStatus() != StocktakingModel::STATUS_COMPLETED)
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
        if ($this->getStocktakingStatus() == StocktakingModel::STATUS_COMPLETED) {
            switch ($type){
                case 'input':
                    return static::TMPL_TEXT_LABEL;
                    break;
                case 'date':
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

        if ($this->getStocktakingStatus() != null) {
            if ($type == 'select'){
                return static::TMPL_SELECT_LABEL;
            }
        }
        return parent::getModifyTmpl($type);
    }
}
