<?php

namespace Magecomp\Emailquotepro\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Emailproductquote extends Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_emailproductquote';
        $this->_blockGroup = 'Magecomp_Emailquotepro';
        $this->_headerText = __('Email Cart Statistics');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}