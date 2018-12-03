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

class Import extends \Magento\Backend\App\Action
{

    const CPATH_REMOVE_AFTER = 'activo_bulkimages/dragndrop/removeafterupload';
    
    protected $bulkImageHelper;
    protected $bulkImageImportModel;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Activo\BulkImages\Helper\Data $bulkImageHelper,
        \Activo\BulkImages\Model\Import $bulkImageImportModel
    ) {
        
        $this->bulkImageHelper = $bulkImageHelper;
        $this->bulkImageImportModel = $bulkImageImportModel;
        parent::__construct($context);
    }

    /**
     * Index Action
     * @return Void
     * */
    public function execute()
    {
        $removeafter = $this->getStoreConfig(self::CPATH_REMOVE_AFTER);
        $stats = $this->bulkImageImportModel->processImport(false, true, true, $removeafter);
        $statsencode = json_encode($stats);
        $this->getResponse()->representJson($statsencode);
    }

    public function getStoreConfig($group)
    {
        return $this->bulkImageHelper->getStoreConfig($group);
    }

    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }
}
