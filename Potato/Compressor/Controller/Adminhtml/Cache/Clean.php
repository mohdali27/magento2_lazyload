<?php

namespace Potato\Compressor\Controller\Adminhtml\Cache;

use Potato\Compressor\Controller\Adminhtml\Cache;
use Magento\Backend\App\Action;
use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Helper\File as FileHelper;
use Magento\Framework\App\CacheInterface as AppCache;

/**
 * Class Clean
 */
class Clean extends Cache
{
    /** @var AppCache */
    protected $appCache;

    /** @var FileHelper */
    protected $fileHelper;

    /** @var DataHelper */
    protected $dataHelper;

    /**
     * @param Action\Context $context
     * @param AppCache $appCache
     * @param DataHelper $dataHelper
     * @param FileHelper $fileHelper
     */
    public function __construct(
        Action\Context $context,
        AppCache $appCache,
        DataHelper $dataHelper,
        FileHelper $fileHelper
    ){
        parent::__construct($context);
        $this->appCache = $appCache;
        $this->dataHelper = $dataHelper;
        $this->fileHelper = $fileHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {

            $this->appCache->clean([DataHelper::COMPRESSOR_CACHE_TAG]);
            $this->fileHelper->removeDirectory(
                $this->dataHelper->getRootCachePath(),
                [$this->dataHelper->getImageMergeCachePath()]
            );
            $this->messageManager->addSuccessMessage(
                __('Cache has been successfully cleaned')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong')
            );
        }
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}