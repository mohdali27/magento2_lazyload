<?php
/**
 * Activo Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Activo Commercial License
 * that is available through the world-wide-web at this URL:
 * http://extensions.activo.com/license_professional
 *
 * @copyright   Copyright (c) 2017 Activo Extensions (http://extensions.activo.com)
 * @license     Commercial
 */
namespace Activo\BulkImages\Block\Adminhtml;

use \Magento\Backend\Block\Widget\Grid\Container;

class Bulkimages extends Container
{

    /**
     * Modify header & button labels
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_headerText = __('Manage Bulk Images');
        parent::_construct();
        $this->removeButton('add');
        $this->buttonList->add(
            'importall',
            [
            'label' => __('Import All Images'),
            'onclick' => "location.href='" . $this->getUrl('*/*/importall') . "'",
            'class' => 'add primary'
            ]
        );
        $this->buttonList->add(
            'importnew',
            [
            'label' => __('Import New Images'),
            'onclick' => "location.href='" . $this->getUrl('*/*/importnew') . "'",
            'class' => 'add primary'
            ]
        );
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-bulkimages-impoert';
    }
}
