<?php
namespace Potato\Compressor\Model;

use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Helper\File as FileHelper;
use Magento\Framework\App\CacheInterface as AppCache;

class CacheManager
{
    const CACHE_KEY_FOR_SIZE_VALUE = 'POTATO_COMPRESSOR_FILESYSTEM_CACHE_SIZE';

    /** @var DataHelper */
    protected $dataHelper;

    /** @var FileHelper */
    protected $fileHelper;

    /** @var AppCache */
    protected $appCache;

    /**
     * CacheManager constructor.
     *
     * @param DataHelper $dataHelper
     * @param FileHelper $fileHelper
     * @param AppCache $appCache
     */
    public function __construct(
        DataHelper $dataHelper,
        FileHelper $fileHelper,
        AppCache $appCache
    ) {
        $this->dataHelper = $dataHelper;
        $this->fileHelper = $fileHelper;
        $this->appCache = $appCache;
    }

    /**
     * @return float
     */
    public function getCacheSize()
    {
        $cacheSize = $this->appCache->load(self::CACHE_KEY_FOR_SIZE_VALUE);
        return floatval($cacheSize);
    }

    /**
     * @return $this
     */
    public function calculateCacheSize()
    {
        $value = $this->fileHelper->getFolderSize($this->dataHelper->getRootCachePath()) / 1024 / 1024;
        $this->appCache->save(
            $value,
            self::CACHE_KEY_FOR_SIZE_VALUE,
            [DataHelper::COMPRESSOR_CACHE_TAG]
        );
        return $this;
    }
}