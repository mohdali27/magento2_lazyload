<?php
/**
 * Solwin Infotech
 * Solwin Ournews Extension
 *
 * @category   Solwin
 * @package    Solwin_Ournews
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
?>
<?php

namespace Solwin\Ournews\Model\News;

class Image
{
    /**
     * Media sub folder
     * 
     * @var string
     */
    protected $_subDir = 'solwin/ournews/news';

    /**
     * URL builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * File system model
     * 
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * constructor
     * 
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Filesystem $fileSystem
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Filesystem $fileSystem
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_fileSystem = $fileSystem;
    }

    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_urlBuilder
                ->getBaseUrl(
                ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA])
                .$this->_subDir.'/image';
    }
    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->_fileSystem->getDirectoryWrite(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
                )
                ->getAbsolutePath($this->_subDir.'/image');
        
    }
}