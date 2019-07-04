<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\ManageStock;

use Magento\Framework\App\ResponseInterface;

class Index extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::warehouse_stock_view';

    /**
     * @var \Magestore\InventorySuccess\Controller\Adminhtml\Context
     */
    protected $_context;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
    ){
        parent::__construct($context);
        $this->_context = $context;
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->_context->getResultPageFactory()->create();
        $resultPage->setActiveMenu('Magestore_InventorySuccess::warehouse_stock_view');
        $resultPage->addBreadcrumb(__('Manage Stock'), __('Manage Stock'));
        $resultPage->addBreadcrumb(__('Manage Stock'), __('Manage Stock'));
        return $resultPage;
    }

    /**
     * Manage Stock page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();

        $resultPage->getConfig()->getTitle()->prepend(__('Manage Stock'));
        $resultPage->getConfig()->getTitle()
            ->prepend(__('Manage Stock'));

        return $resultPage;
    }
}