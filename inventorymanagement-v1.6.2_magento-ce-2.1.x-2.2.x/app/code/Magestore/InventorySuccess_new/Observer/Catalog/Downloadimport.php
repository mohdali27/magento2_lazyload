<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Catalog;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\ImportExport\Controller\Adminhtml\Import as ImportController;
use Magento\Framework\App\Filesystem\DirectoryList;

class Downloadimport implements ObserverInterface
{

    const SAMPLE_FILES_MODULE = 'Magento_ImportExport';
    const SAMPLE_FILES_MODULE_MODIFY = 'Magestore_InventorySuccess';

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;
    
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    protected $warehouseCollection;
    
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->messageManager = $context->getMessageManager();
        $this->resultRedirectFactory = $redirectFactory;
        $this->_actionFlag = $context->getActionFlag();
        $this->warehouseCollection = $warehouseCollectionFactory->create();
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        if ($request->getParam('filename') == 'catalog_product') {
            $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::SAMPLE_FILES_MODULE_MODIFY);
            $fileName = 'catalog_product_with_warehouse.csv';
            $fileAbsolutePath = $moduleDir . '/Observer/Catalog/Files/Sample/' . $fileName;
            $directoryRead = $this->readFactory->create($moduleDir);
            $filePath = $directoryRead->getRelativePath($fileAbsolutePath);
            if (!$directoryRead->isFile($filePath)) {
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $this->messageManager->addError(__('There is no sample file for this entity.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/import');
                return $resultRedirect;
            }
            $content = file_get_contents($fileAbsolutePath);
            // Get real warehouse data
            $this->warehouseCollection
                ->setOrder('warehouse_id', 'ASC')
                ->setPageSize(2);
            $warehouseIds = [];
            foreach ($this->warehouseCollection as $warehouse) {
                array_unshift($warehouseIds, $warehouse->getId());
            }
            $index = 2;
            foreach ($warehouseIds as $warehouseId) {
                $content = str_replace([
                    'qty_' . $index,
                    'location_' . $index,
                ], [
                    'qty_' . $warehouseId,
                    'location_' . $warehouseId,
                ], $content);
                $index--;
            }
            return $this->fileFactory->create(
                $fileName,
                $content,
                DirectoryList::VAR_DIR
            );
        }
    }
}