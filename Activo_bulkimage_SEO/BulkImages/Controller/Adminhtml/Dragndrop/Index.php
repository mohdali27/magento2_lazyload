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
namespace Activo\BulkImages\Controller\Adminhtml\Dragndrop;

class Index extends \Magento\Backend\App\Action
{

    /**
     * Index Action
     * @return Void
     * */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Catalog::catalog');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Bulk Images: Drag & Drop'));
        $this->_addBreadcrumb(__('Bulk Images: Drag & Drop'), __('Bulk Images: Drag & Drop'));
        $this->_view->renderLayout();
    }
}
