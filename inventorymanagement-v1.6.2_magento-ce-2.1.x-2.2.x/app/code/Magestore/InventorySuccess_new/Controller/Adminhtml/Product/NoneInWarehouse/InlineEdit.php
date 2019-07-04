<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Product\NoneInWarehouse;

use Magento\Cms\Api\PageRepositoryInterface as PageRepository;
use Magento\Framework\Controller\Result\JsonFactory;
/**
 * Class InlineEdit
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Product\NoneInWarehouse
 */
class InlineEdit extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{
    /** @var PageRepository  */
    protected $pageRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;
    
    
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        PageRepository $pageRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->pageRepository = $pageRepository;
        $this->jsonFactory = $jsonFactory;
        
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        $postItems = $this->_prepareData($postItems);
        $this->_warehouseFactory->create()->addProductsInline($postItems);
//        if(count($results)>0){
//            $error = true;
//            foreach ($results as $id){
//                $messages[] = $this->getErrorProductId($id);
//            }
//        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Prepare data.
     *
     * @param array $postData
     * @return array
     */
    protected function _prepareData($postData = [])
    {
        $result = [];
        foreach ($postData as $item){
            $result[$item['warehouse_id']] = isset($result[$item['warehouse_id']])?$result[$item['warehouse_id']]:[];
            array_push($result[$item['warehouse_id']], $item['entity_id']);
        }
        return $result;
    }

    /**
     * Add product id to error message
     *
     * @param int $id
     * @return string
     */
    protected function getErrorProductId($id)
    {
        return __('[Product ID: %1] Cannot add this product to the warehouse',$id);
    }

}