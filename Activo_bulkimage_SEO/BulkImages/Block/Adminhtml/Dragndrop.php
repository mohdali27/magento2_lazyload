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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\Block\Template\Context;

class Dragndrop extends \Magento\Backend\Block\Template
{

    protected $filesystem;
    protected $scopeConfig;

    /**
     * Dragndrop constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
    
        $this->filesystem = $context->getFilesystem();
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }
    
    public function getMediaDir()
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        return $mediaDirectory->getAbsolutePath(ltrim($this->scopeConfig->getValue(\Activo\BulkImages\Controller\Adminhtml\Dragndrop\Upload::CPATH_UPLOAD_FOLDER)));
    }
}
