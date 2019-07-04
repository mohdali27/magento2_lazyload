<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created by PhpStorm.
 * User: Eden Duong
 * Date: 25/08/2016
 * Time: 9:09 SA
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\Import;

use Magestore\InventorySuccess\Model\Stocktaking;

/**
 * Class Form
 * @package Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\Import
 */
class Form extends  \Magestore\InventorySuccess\Block\Adminhtml\Import\Form {

    /**
     * @var \Magestore\InventorySuccess\Model\StocktakingFactory
     */
    protected $stocktakingFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking
     */
    protected $stocktakingResource;

    /**
     * Helper Data
     *
     * @var \Magestore\InventorySuccess\Helper\Data
     */
    protected $helper;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking $stocktakingResource
     * @param \Magestore\InventorySuccess\Model\StocktakingFactory $stocktakingFactory
     * @param \Magestore\InventorySuccess\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking $stocktakingResource,
        \Magestore\InventorySuccess\Model\StocktakingFactory $stocktakingFactory,
        \Magestore\InventorySuccess\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->urlBuilder = $context->getUrlBuilder();
        $this->stocktakingFactory = $stocktakingFactory;
        $this->stocktakingResource = $stocktakingResource;
        $this->helper = $helper;
        $this->setUseContainer(true);
    }

    /**
     * Get adjust stock csv sample link
     *
     * @return mixed
     */
    public function getCsvSampleLink($isBarcode = false) {
        $isQty = ($this->getStocktakingStatus() == Stocktaking::STATUS_PROCESSING) ? true : false;
            $url = $this->getUrl('inventorysuccess/stocktaking/downloadsample',
                    array(
                        '_secure' => true,
                        'id' => $this->getRequest()->getParam('id'),
                        'is_qty' => $isQty,
                        'is_barcode' => $isBarcode
                    ));
        return $url;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent() {
        return 'Please choose a CSV file to import product to stocktake. You can download this sample CSV file';
    }

    /**
     * Get import urk
     *
     * @return mixed
     */
    public function getImportLink() {
        $status = Stocktaking::STATUS_PENDING;
        if($this->getStocktakingStatus() == Stocktaking::STATUS_PROCESSING){
            $status = Stocktaking::STATUS_PROCESSING;
        }
        return $this->getUrl('inventorysuccess/stocktaking/import',
                            array(
                                'id' => $this->getRequest()->getParam('id'),
                                  'status' => $status,
                                  '_secure' => true
                            ));
    }

    /**
     * Get stocktaking status
     *
     * @return int
     */
    public function getStocktakingStatus() {
        $stocktakingId = $this->getRequest()->getParam('id');
        $stocktaking = $this->stocktakingFactory->create();
        $this->stocktakingResource->load($stocktaking, $stocktakingId);
        if($stocktaking->getId()){
            return $stocktaking->getStatus();
        }
        return 0;
    }

    /**
     * Get import title
     *
     * @return string
     */
    public function getTitle() {
        return 'Import products';
    }

    /**
     * is barcode
     *
     * @return boolean
     */
    public function isBarcode() {
        return $this->helper->getBarcodeModuleEnable();
    }

}