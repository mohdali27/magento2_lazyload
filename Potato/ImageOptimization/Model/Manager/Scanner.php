<?php
namespace Potato\ImageOptimization\Model\Manager;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Potato\ImageOptimization\Lib\FileFinder\FileFinder;
use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\Config;

/**
 * Class Scanner
 */
class Scanner
{
    const START_FILE_CACHE_ID = 'po_image_optimization_START_FILE_CACHE_ID';
    const SCAN_DATABASE_STEP = 500;
    const SCAN_DATABASE_STATUS_CACHE_KEY = 'po_imageoptimization_SCAN_DATABASE_STATUS';

    /** @var CacheInterface  */
    protected $cache;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var ImageRepositoryInterface  */
    protected $imageRepository;

    /** @var Logger  */
    protected $logger;

    /** @var Config  */
    protected $config;
    
    protected $cachePostfix = null;
    
    protected $callbackCount = 0;

    protected $limit = null;
    
    protected $timeLimit = null;
    
    protected $timeStart = null;
    
    protected $originalMaxNestingLevel = null;
    
    protected $callback = null;

    protected $excludeDirs = [];

    /**
     * Scanner constructor.
     * @param ImageRepositoryInterface $imageRepository
     * @param CacheInterface $cache
     * @param Logger $logger
     * @param Filesystem $filesystem
     * @param Config $config
     */
    public function __construct(
        ImageRepositoryInterface $imageRepository,
        CacheInterface $cache,
        Logger $logger,
        Filesystem $filesystem,
        Config $config
    ) {
        $this->cache = $cache;
        $this->filesystem = $filesystem;
        $this->imageRepository = $imageRepository;
        $this->logger = $logger;
        $this->config = $config;
        $this->excludeDirs = $this->config->getExcludeDirs();
    }

    /**
     * @param null $limit
     * @return $this
     * @throws \Exception
     */
    public function saveImageGalleryFiles($limit = null)
    {
        $includeDirs = $this->getDirs();
        foreach ($includeDirs as $dir) {
            $this->prepareImagesFromDir(rtrim($dir, '/'), null, $limit);
            if (null !== $limit) {
                $limit -= $this->callbackCount;
            }
        }
        return $this;
    }

    protected function getDirs()
    {
        $includeDirs = $this->config->getIncludeDirs();
        $dirs = [];
        foreach ($includeDirs as $includeDir) {
            $fullPath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath()
                . $includeDir;
            $fullPath = realpath($fullPath);
            if (!$fullPath) {
                continue;
            }
            $dirs[] = $fullPath;
        }
        if (!$dirs) {
            //if all included dirs invalid or deleted - run scanner for default static and media dirs
            $staticPath = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->getAbsolutePath();
            $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $dirs = [$staticPath, $mediaPath];
        }
        return $dirs;
    }

    /**
     * @param string $dirPath
     * @param string|null $startPath
     * @param int|null $limit
     * @return $this
     * @throws \Exception
     */
    public function prepareImagesFromDir($dirPath, $startPath = null, $limit = null)
    {
        $this->cachePostfix = md5($dirPath);
        if (null === $startPath && $this->cache->getFrontend()->test(self::START_FILE_CACHE_ID . $this->cachePostfix)) {
            $startPath = $this->cache->load(self::START_FILE_CACHE_ID . $this->cachePostfix);
        }
        $this->limit = $limit;
        $fileFinder = new FileFinder([
            'dir' => $dirPath,
            'callback' => array($this, 'saveFilePath'),
            'is_folder_path_excluded_callback' => array($this, 'isFilePathExcluded'),
            'start_path' => $startPath
        ]);
        $fileFinder->find();
        $this->cache->remove(self::START_FILE_CACHE_ID . $this->cachePostfix);
        return $this;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    public function saveFilePath($filePath)
    {
        if (
            null !== $this->timeLimit && null !== $this->timeStart 
            && $this->timeLimit <= time() - $this->timeStart
        ) {
            return false;
        }
        if (null !== $this->limit && $this->callbackCount >= $this->limit) {
            return false;
        }
        $this->cache->save($filePath, self::START_FILE_CACHE_ID . $this->cachePostfix);
        $result = null;

        if ($this->isFilePathExcluded($filePath)) {
            return true;
        }

        if ($this->imageRepository->isPathExist($filePath) || !$this->imageRepository->getImageType($filePath)) {
            return true;
        }
        $image = $this->imageRepository->create();
        $image
            ->setPath($filePath)
            ->setStatus(StatusSource::STATUS_PENDING)
        ;
        try {
            $this->imageRepository->save($image);
            $this->callbackCount++;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        if (null !== $this->callback) {
            call_user_func($this->callback, $this->callbackCount);
        }
        return true;
    }

    /**
     * @param $filePath
     * @return bool
     */
    public function isFilePathExcluded($filePath)
    {
        $localPath = substr_replace($filePath, '', 0, strlen(BP));
        foreach ($this->excludeDirs as $dir) {
            if (false !== strpos($localPath, DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR)) {
                //local file path has excluded dirs
                return true;
            }
        }
        return false;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function updateImagesFromDatabase()
    {
        $curPage = $this->cache->getFrontend()->test(self::SCAN_DATABASE_STATUS_CACHE_KEY) ? $this->cache->load(self::SCAN_DATABASE_STATUS_CACHE_KEY) : false;
        if (!$curPage) {
            $curPage = 0;
        }
        $previousImagesUpdated = $curPage * self::SCAN_DATABASE_STEP;
        $curPage++;
        $imageList = $this->imageRepository->getListPerPagination(self::SCAN_DATABASE_STEP, $curPage);
        if ($curPage > $imageList->getLastPageNumber()) {
            $this->cache->remove(self::SCAN_DATABASE_STATUS_CACHE_KEY);
            return $this;
        }
        $imagesFound = count($imageList->getItems());
        foreach ($imageList->getItems() as $item) {
            if (!file_exists($item->getPath())) {
                $this->imageRepository->delete($item);
                continue;
            }
            if ((filemtime($item->getPath()) <= $item->getTime())
                || $item->getStatus() !== StatusSource::STATUS_OPTIMIZED
            ) {
                continue;
            }
            try {
                $item->setStatus(StatusSource::STATUS_OUTDATED);
                $this->imageRepository->save($item);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        if (null !== $this->callback) {
            call_user_func($this->callback, $previousImagesUpdated + $imagesFound);
        }
        $this->cache->save($curPage, self::SCAN_DATABASE_STATUS_CACHE_KEY);
        return $this;
    }

    /**
     * @param int $timeLimit
     */
    public function setTimeLimit($timeLimit)
    {
        $this->timeLimit = $timeLimit;
    }

    /**
     * @param int $timeStart
     */
    public function setTimeStart($timeStart)
    {
        $this->timeStart = $timeStart;
    }

    public function setCallback($param)
    {
        $this->callback = $param;
        return $this;
    }
}
