<?php
namespace Potato\Compressor\Cron;

use Potato\Compressor\Model\CacheManager as CacheManager;
use Potato\Compressor\Model\Config as Config;

class CacheCalculate
{
    /** @var CacheManager */
    protected $cacheManager;

    /** @var Config */
    protected $config;

    /**
     * @param CacheManager $cacheManager
     * @param Config $config
     */
    public function __construct(
        CacheManager $cacheManager,
        Config $config
    ) {
        $this->cacheManager = $cacheManager;
        $this->config = $config;
    }

    public function execute()
    {
        if (null === $this->config->getCacheMaxSize()) {
            return;
        }
        $this->cacheManager->calculateCacheSize();
    }
}
