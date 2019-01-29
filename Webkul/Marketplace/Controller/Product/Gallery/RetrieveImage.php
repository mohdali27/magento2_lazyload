<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Product\Gallery;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList as FilesystemDirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\File\Uploader;

use Magento\Framework\Controller\Result\RawFactory;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File;

/**
 * Marketplace Product RetrieveImage controller.
 */
class RetrieveImage extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Config
     */
    protected $_mediaConfig;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var AbstractAdapter
     */
    protected $_imageAdapter;

    /**
     * @var Curl
     */
    protected $_curl;

    /**
     * @var File
     */
    protected $_fileUtility;

    /**
     * @param Context        $context
     * @param RawFactory     $resultRawFactory
     * @param Config         $mediaConfig
     * @param Filesystem     $fileSystem
     * @param AdapterFactory $imageAdapterFactory
     * @param Curl           $curl
     * @param File           $fileUtility
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        Config $mediaConfig,
        Filesystem $fileSystem,
        AdapterFactory $imageAdapterFactory,
        Curl $curl,
        File $fileUtility
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->_mediaConfig = $mediaConfig;
        $this->_fileSystem = $fileSystem;
        $this->_imageAdapter = $imageAdapterFactory->create();
        $this->_curl = $curl;
        $this->_fileUtility = $fileUtility;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $baseTmpMediaPath = $this->_mediaConfig->getBaseTmpMediaPath();
        try {
            $remoteImageUrl = $this->getRequest()->getParam('remote_image');
            $baseFileName = basename($remoteImageUrl);
            $localFileName = Uploader::getCorrectFileName($baseFileName);
            $localTmpFileName = Uploader::getDispretionPath($localFileName).DIRECTORY_SEPARATOR.$localFileName;
            $localFileMediaPath = $baseTmpMediaPath.($localTmpFileName);
            $localUniqueFileMediaPath = $this->getNewFileName($localFileMediaPath);
            $this->saveRemoteImage($remoteImageUrl, $localUniqueFileMediaPath);
            $localFileFullPath = $this->getDestinationFileAbsolutePath($localUniqueFileMediaPath);
            $this->_imageAdapter->validateUploadFile($localFileFullPath);
            $result = $this->appendResultSaveRemoteImage($localUniqueFileMediaPath);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));

        return $response;
    }

    /**
     * @param string $fileName
     *
     * @return mixed
     */
    protected function appendResultSaveRemoteImage($fileName)
    {
        $fileInfo = pathinfo($fileName);
        $tmpFileName = Uploader::getDispretionPath($fileInfo['basename']).DIRECTORY_SEPARATOR.$fileInfo['basename'];
        $result['name'] = $fileInfo['basename'];
        $result['type'] = $this->_imageAdapter->getMimeType();
        $result['error'] = 0;
        $result['size'] = filesize($this->getDestinationFileAbsolutePath($fileName));
        $result['url'] = $this->_mediaConfig->getTmpMediaUrl($tmpFileName);
        $result['file'] = $tmpFileName;

        return $result;
    }

    /**
     * @param string $fileUrl
     * @param string $localFilePath
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveRemoteImage($fileUrl, $localFilePath)
    {
        $this->_curl->setConfig(['header' => false]);
        $this->_curl->write('GET', $fileUrl);
        $image = $this->_curl->read();
        if (empty($image)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not get preview image information. Please check your connection and try again.')
            );
        }
        $this->_fileUtility->saveFile($localFilePath, $image);
    }

    /**
     * @param string $localFilePath
     *
     * @return string
     */
    protected function getNewFileName($localFilePath)
    {
        $destinationFile = $this->getDestinationFileAbsolutePath($localFilePath);
        $fileName = Uploader::getNewFileName($destinationFile);
        $fileInfo = pathinfo($localFilePath);

        return $fileInfo['dirname'].DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * @param string $localTmpFile
     *
     * @return string
     */
    protected function getDestinationFileAbsolutePath($localTmpFile)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->_fileSystem->getDirectoryRead(FilesystemDirectoryList::MEDIA);
        $pathToSave = $mediaDirectory->getAbsolutePath();

        return $pathToSave.$localTmpFile;
    }
}
