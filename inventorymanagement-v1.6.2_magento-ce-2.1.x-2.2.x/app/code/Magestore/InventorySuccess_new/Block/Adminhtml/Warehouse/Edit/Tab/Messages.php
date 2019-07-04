<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab;

use Magento\Framework\Message\MessageInterface;
/**
 * Class Messages
 * @package Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab
 */
class Messages extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::warehouse/messages.phtml';
    protected $_successMessage = '';
    protected $_errorMessage = '';
    protected $_warningMessage = '';

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $messages = $this->messageManager->getMessages(true)->getItems();
        foreach ($messages as $message){
            if($message->getType() == MessageInterface::TYPE_SUCCESS){
                $this->_successMessage .= ($this->_successMessage==''?$message->getText():'<br>'.$message->getText());
            }
            if($message->getType() == MessageInterface::TYPE_ERROR){
                $this->_errorMessage .= ($this->_errorMessage==''?$message->getText():'<br>'.$message->getText());
            }
            if($message->getType() == MessageInterface::TYPE_WARNING){
                $this->_warningMessage .= ($this->_warningMessage==''?$message->getText():'<br>'.$message->getText());
            }
        }
    }

    public function getSuccessMesssages(){
        return $this->_successMessage;
    }

    public function getErrorMessages(){
        return $this->_errorMessage;
    }

    public function getWarningMessages(){
        return $this->_warningMessage;
    }
}