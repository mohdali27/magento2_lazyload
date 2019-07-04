<?php
namespace Potato\Compressor\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Helper\File as FileHelper;
use Magento\Framework\App\CacheInterface as AppCache;

class RefreshCache implements ObserverInterface
{
    /** @var AppCache */
    protected $appCache;
    
    /** @var FileHelper  */
    protected $fileHelper;
    
    /** @var DataHelper  */
    protected $dataHelper;

    /**
     * RefreshCache constructor.
     * @param FileHelper $fileHelper
     * @param DataHelper $dataHelper
     * @param AppCache $appCache
     */
    public function __construct(
        FileHelper $fileHelper,
        DataHelper $dataHelper,
        AppCache $appCache
    ) {
        $this->fileHelper = $fileHelper;
        $this->dataHelper = $dataHelper;
        $this->appCache = $appCache;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $this->appCache->clean([DataHelper::COMPRESSOR_CACHE_TAG]);
        $this->fileHelper->removeDirectory(
            $this->dataHelper->getRootCachePath(),
            [$this->dataHelper->getImageMergeCachePath()]
        );
        return $this;
    }
}