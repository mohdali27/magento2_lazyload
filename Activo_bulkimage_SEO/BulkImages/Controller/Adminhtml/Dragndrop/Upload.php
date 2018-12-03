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

use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Magento\Backend\App\Action
{

    const PATH_LOG_FILE = 'activo_bulkimages.log';
    const CPATH_SOURCE_FOLDER = 'activo_bulkimages/global/sourcefolder';
    const CPATH_LOGGING = 'activo_bulkimages/global/logging';
    const CPATH_UPLOAD_FOLDER = 'activo_bulkimages/dragndrop/uploadfolder';

    private $fileSystem;
    private $uploader;
    protected $logger;
    protected $resultRawFactory;
    protected $bulkImageHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Activo\BulkImages\Logger\Logger $logger,
        \Magento\Framework\Filesystem $fileSystem,
        \Activo\BulkImages\Helper\Data $bulkImageHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploader,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
    
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;
        $this->bulkImageHelper = $bulkImageHelper;
        $this->resultRawFactory = $resultRawFactory;
        $this->uploader = $uploader;
        parent::__construct($context);
    }

    /**
     * Index Action
     * @return mixed
     * */
    public function execute()
    {
        
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $advanced_logging = $this->getStoreConfig(self::CPATH_LOGGING);
        $importFolder = $mediaDirectory->getAbsolutePath(ltrim($this->getStoreConfig(self::CPATH_UPLOAD_FOLDER)));

        // Create the upload folder if it does not exist
        if (!file_exists($importFolder)) {
            mkdir($importFolder, 0777, true);
        }

        if ($advanced_logging) {
            $this->logger->info('Drug-and-Drop upload started. Target folder: ' . $importFolder);
        }

        $uploader = $this->uploader->create(['fileId' => 'file']);

        if (!empty($uploader->validateFile())) {
            $mainFile = $uploader->validateFile();
            $tempFile = $mainFile['tmp_name'];
            $targetPath = $importFolder;
            $targetFile = $targetPath . '/' . $mainFile['name'];
            
            move_uploaded_file($tempFile, $targetFile);
            if ($advanced_logging) {
                $this->logger->info('Drug-and-Drop uploaded file: ' . $targetFile);
            }
        }

        $response = $this->resultRawFactory->create();
        $response->setContents($mainFile['name']);
        return $response;
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
