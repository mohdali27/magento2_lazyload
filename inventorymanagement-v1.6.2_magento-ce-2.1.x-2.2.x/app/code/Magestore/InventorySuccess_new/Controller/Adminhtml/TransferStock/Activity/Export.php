<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Activity;

use Magestore\InventorySuccess\Api\Data\TransferStock\TransferActivityInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{
    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory
     */
    protected $_transferActivityFactory;

    protected $csvProcessor;
    protected $fileFactory;
    protected $filesystem;

    /** @var  \Magestore\InventorySuccess\Model\Locator\LocatorFactory $_locatorFactory */
    protected $_locatorFactory;

    /** @var \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivityProduct\CollectionFactory  $_collection */
    protected $_collection;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivityProduct\CollectionFactory  $_collection,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ){
        parent::__construct($context);
        $this->_transferActivityFactory = $context->getTransferActivityFactory();
        $this->_collection = $_collection;
        $this->csvProcessor = $csvProcessor;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
    }
    public function execute()
    {
        $id = $this->_request->getParam("id");
        /** @var \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $transferActivity = $this->_transferActivityFactory->create();
        if($id){
            $transferActivity->load($id);
        }
        $activity_type = $transferActivity->getActivityType();
        if($activity_type == TransferActivityInterface::ACTIVITY_TYPE_RETURN){
            $name = 'returned_list.csv';
        }elseif($activity_type == TransferActivityInterface::ACTIVITY_TYPE_RECEIVING){
            $name = 'received_list.csv';
        }elseif($activity_type == TransferActivityInterface::ACTIVITY_TYPE_DELIVERY){
            $name = 'delivered_list.csv';
        }else{
            $name = 'products_list.csv';
        }
        $this->getBaseDirMedia()->create('magestore/inventory/transferstock');
        $filename = $this->getBaseDirMedia()->getAbsolutePath('magestore/inventory/transferstock/'.$name);
        $data = array(
            array('Name',
                'SKU',
                'QTY'
            )
        );
        $data = array_merge($data, $this->generateSampleData());
        $this->csvProcessor->saveData($filename, $data);
        return $this->fileFactory->create(
            $name,
            file_get_contents($filename),
            DirectoryList::VAR_DIR
        );
    }

    /**
     * get base dir media
     *
     * @return string
     */
    public function getBaseDirMedia()
    {
        return $this->filesystem->getDirectoryWrite('media');
    }

    public function generateSampleData() {
        $id = $this->_request->getParam("id");
        $data = array();
        $transferActivityProduct = $this->_collection->create();
        if($id){
            $transferActivityProduct->addFieldToFilter('activity_id',$id);
        }
        foreach ($transferActivityProduct as $product) {
                $data[]= array(
                    $product->getData('product_name'),
                    $product->getData('product_sku'),
                    $product->getData('qty'),
                );
        }
        return $data;
    }

}


