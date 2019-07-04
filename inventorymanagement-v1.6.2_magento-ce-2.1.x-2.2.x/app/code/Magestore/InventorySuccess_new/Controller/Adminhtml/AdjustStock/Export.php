<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Class Import
 * @package Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock
 */
class Export extends \Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock\AdjustStock
{
    const SAMPLE_QTY = 1;
    
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->getBaseDirMedia()->create('magestore/inventory/adjuststock');
        $filename = $this->getBaseDirMedia()->getAbsolutePath('magestore/inventory/adjuststock/adjusted_products.csv');
        
        $data = array(
            array(__('No'), __('Product Name'), __('SKU'), __('Old Qty'), __('Change Qty'), __('Adjusted Qty'))
        );
        $data = array_merge($data, $this->getProductCollection());
        $this->csvProcessor->saveData($filename, $data);
        return $this->fileFactory->create(
            'adjusted_products.csv',
            file_get_contents($filename),
            DirectoryList::VAR_DIR
        );
    }

    /**
     * get csv url
     *
     * @return string
     */
    public function getCsvLink()
    {
        $path = 'magestore/inventory/adjuststock/adjusted_products.csv';
        $url =  $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
        return $url;
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

    /**
     * get adjusted product collection
     *
     * @param 
     * @return array
     */
    public function getProductCollection()
    {
        $adjustStockId = $this->getRequest()->getParam('id');
        $data = array();
        if(isset($adjustStockId)){
            $adjustStockManagement = $this->adjustStockManagement;
            $adjustStock = $this->adjustStockFactory->create();
            $adjustStock->setId($adjustStockId);
            $productCollection = $adjustStockManagement->getProducts($adjustStock);
            $number = 1;
            foreach ($productCollection as $productModel) {
                $data[]= array(
                    $number,
                    $productModel->getData('product_name'),
                    $productModel->getData('product_sku'),
                    $productModel->getData('old_qty'),
                    $productModel->getData('change_qty'),
                    $productModel->getData('adjust_qty')
                );
                $number ++;
            }
        }
        return $data;
    }
}
