<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\DeleteProduct;

use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\Collection;
use Magento\Framework\Message\MessageInterface;

/**
 * Class Grid
 * @package Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Product
 */
class Grid extends \Magestore\InventorySuccess\Block\Adminhtml\ManageStock\AbstractGridProduct
{
    
    protected $_hiddenInputField = 'delete_products';
    
    protected $_successMessage = '';
    protected $_errorMessage = '';

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId("warehouse_list_delete_products");
        $messages = $this->messageManager->getMessages(true)->getItems();
        foreach ($messages as $message){
            if($message->getType() == MessageInterface::TYPE_SUCCESS){
                $this->_successMessage .= ($this->_successMessage==''?$message->getText():'<br>'.$message->getText());
            }
            if($message->getType() == MessageInterface::TYPE_ERROR){
                $this->_errorMessage .= ($this->_errorMessage==''?$message->getText():'<br>'.$message->getText());
            }
        }
    }
    
    public function modifyCollection($collection){
        $collection->addWarehouseToFilter($this->getRequest()->getParam('id'))
            ->getNoneWarehouseProduct();
        return $collection;
    }
    
    public function modifyColumns(){
        $this->addColumn("sum_total_qty",
            [
                "header" => __("Total Qty"),
                "index" => "sum_total_qty",
                'type' => 'number',
                "sortable" => true,
                'filter_condition_callback' => array($this, '_filterTotalQtyCallback')
            ]
        );
        $this->addColumn("shelf_location",
            [
                "header" => __("Shelf Location"),
                "index" => "shelf_location",
                "sortable" => true,
            ]
        );
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl("*/warehouse_product_delete/grid", ["_current" => true]);
    }

    /**
     * @return string
     */
    public function getDeleteProductUrl()
    {
        return $this->getUrl("*/warehouse_product_delete/delete", ["_current" => true]);
    }
    
    public function getJsObjectParent(){
        return $this->getLayout()->createBlock('Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Stock\Grid')
            ->getJsObjectName();
    }
    
    public function getMessages(){
        $html = '<div class="messages">';
        if($this->getSuccessMesssages()!=''){
            $html.= '<div class="message message-success success">'.
                '<div data-ui-id="messages-message-success">'.$this->getSuccessMesssages().'</div></div>';
        }
        if($this->getErrorMessages()!=''){
            $html.= '<div class="message message-error error"><div data-ui-id="messages-message-error">'.
                $this->getErrorMessages().'</div></div>';
        }
        $html.='</div>';
        return $html;
    }
    
    public function getSuccessMesssages(){
        return $this->_successMessage;
    }
    
    public function getErrorMessages(){
        return $this->_errorMessage;
    }
}