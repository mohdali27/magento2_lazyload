<?php

namespace Potato\ImageOptimization\Cron;

use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\Manager\Image as ImageManager;
use Magento\Framework\App\CacheInterface;

class Optimization
{
    /** @var ImageRepositoryInterface  */
    protected $imageRepository;

    /** @var Config  */
    protected $config;

    /** @var Logger  */
    protected $logger;

    /** @var ImageManager  */
    protected $imageManager;

    /** @var CacheInterface  */
    protected $cache;

    /**
     * Optimization constructor.
     * @param ImageRepositoryInterface $imageRepository
     * @param Config $config
     * @param Logger $logger
     * @param ImageManager $imageManager
     * @param CacheInterface $cache
     */
    public function __construct(
        ImageRepositoryInterface $imageRepository,
        Config $config,
        Logger $logger,
        ImageManager $imageManager,
        CacheInterface $cache
    ) {
        $this->imageRepository = $imageRepository;
        $this->config = $config;
        $this->logger = $logger;
        $this->imageManager = $imageManager;
        $this->cache = $cache;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->config->isEnabled() || true === $this->cache->getFrontend()->test(Config::OPTIMIZATION_RUNNING_CACHE_KEY)) {
            return $this;
        }
        $this->cache->save(true, Config::OPTIMIZATION_RUNNING_CACHE_KEY);
        $imageCollection = $this->imageRepository->getNeedToOptimizationList()->getItems();
        $this->imageManager->optimizeImageCollection($imageCollection);
        $this->cache->remove(Config::OPTIMIZATION_RUNNING_CACHE_KEY);
        return $this;
    }
}
