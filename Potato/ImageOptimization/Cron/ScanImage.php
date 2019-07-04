<?php

namespace Potato\ImageOptimization\Cron;

use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Manager\Scanner;
use Potato\ImageOptimization\Logger\Logger;
use Magento\Framework\App\CacheInterface;

class ScanImage
{
    const SCAN_FILESYSTEM_TIME_LIMIT = 30;
    const SCAN_FILESYSTEM_STAGE = 'filesystem';
    const SCAN_DATABASE_STAGE = 'database';
    const SCAN_STAGE_CACHE_KEY = 'po_imageoptimization_SCAN_STAGE';
    
    /** @var Config  */
    protected $config;

    /** @var Logger  */
    protected $logger;

    /** @var Scanner  */
    protected $fileScanner;

    /** @var CacheInterface  */
    protected $cache;

    /**
     * ScanImage constructor.
     * @param Config $config
     * @param Logger $logger
     * @param Scanner $fileScanner
     * @param CacheInterface $cache
     */
    public function __construct(
        Config $config,
        Logger $logger,
        Scanner $fileScanner,
        CacheInterface $cache
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->fileScanner = $fileScanner;
        $this->cache = $cache;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->config->isEnabled() || true === $this->cache->getFrontend()->test(Config::SCAN_RUNNING_CACHE_KEY)) {
            return $this;
        }
        $this->fileScanner->setTimeLimit(self::SCAN_FILESYSTEM_TIME_LIMIT);
        $this->fileScanner->setTimeStart(time());
        $this->cache->save(true, Config::SCAN_RUNNING_CACHE_KEY);
        switch ($this->getScanStage()) {
            case self::SCAN_FILESYSTEM_STAGE:
                $this->fileScanner->saveImageGalleryFiles();
                $this->setScanStage(self::SCAN_DATABASE_STAGE);
                break;
            case self::SCAN_DATABASE_STAGE:
                $this->fileScanner->updateImagesFromDatabase();
                $this->setScanStage(self::SCAN_FILESYSTEM_STAGE);
                break;
            default:
                $this->logger->error('incorrect stage key: ' . $this->getScanStage());
        }
        $this->cache->remove(Config::SCAN_RUNNING_CACHE_KEY);
        return $this;
    }
    
    /**
     * @return string
     */
    protected function getScanStage()
    {
        $stage = $this->cache->getFrontend()->test(self::SCAN_STAGE_CACHE_KEY) ? $this->cache->load(self::SCAN_STAGE_CACHE_KEY) : false;
        if (!$stage) {
            $stage = self::SCAN_FILESYSTEM_STAGE;
            $this->setScanStage($stage);
        }
        return $stage;
    }

    /**
     * @param string $stage
     * @return $this
     */
    protected function setScanStage($stage)
    {
        $this->cache->save($stage, self::SCAN_STAGE_CACHE_KEY);
        return $this;
    }
}
