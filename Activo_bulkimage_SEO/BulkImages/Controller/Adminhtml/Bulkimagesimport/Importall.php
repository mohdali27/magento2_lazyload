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
namespace Activo\BulkImages\Controller\Adminhtml\Bulkimagesimport;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Activo\BulkImages\Model\Import;

class Importall extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Import Activo\BulkImages\Model\Import
     */
    protected $activoImport;

    /**
     * Importall constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Import $activoImport
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Import $activoImport
    ) {
    
        $this->activoImport = $activoImport;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->activoImport->processImport();

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('bulkimages/bulkimagesimport/index');
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::bulkimages');
    }
}
