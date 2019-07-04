<?php
namespace Potato\ImageOptimization\Model\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Store\Model\StoreManagerInterface;
use Potato\ImageOptimization\Logger\Logger;
use Magento\Framework\App\CacheInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Manager\Scanner as ScannerManager;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Helper\ProgressBar;

class Scan extends Command
{
    const INPUT_KEY_LIMIT = 'limit';
    const INPUT_KEY_SEARCH_DIR = 'dir';
    const INPUT_KEY_START_PATH = 'start_path';
    
    /** @var Logger  */
    protected $logger;

    /** @var CacheInterface  */
    protected $cache;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var Config  */
    protected $config;

    /** @var ScannerManager  */
    protected $scanner;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var ProgressBar */
    protected $progress;

    /**
     * Scan constructor.
     * @param StoreManagerInterface $storeManager
     * @param CacheInterface $cache
     * @param Logger $logger
     * @param Config $config
     * @param ScannerManager $scanner
     * @param Filesystem $filesystem
     * @param null $name
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CacheInterface $cache,
        Logger $logger,
        Config $config,
        ScannerManager $scanner,
        Filesystem $filesystem,
        $name = null
    ) {
        parent::__construct($name);
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->scanner = $scanner;
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('po_image_optimization:scan')
            ->setDefinition([
                new InputOption(
                    self::INPUT_KEY_LIMIT,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Scan until found image count < limit'
                ),
                new InputOption(
                    self::INPUT_KEY_SEARCH_DIR,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Scan in this dir'
                ),
                new InputOption(
                    self::INPUT_KEY_START_PATH,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Scan from this path'
                )
            ])
            ->setDescription('Potato Image Optimizer: manually scan via console');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return $this|int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (true === $this->cache->getFrontend()->test(Config::SCAN_RUNNING_CACHE_KEY)) {
            $output->writeln('Scanner is already running. Clean cache if you want run scanner again');
            return $this;
        }
        $this->cache->save(true, Config::SCAN_RUNNING_CACHE_KEY);
        $limit = null;
        if ($input->getOption(self::INPUT_KEY_LIMIT)) {
            $limit = $input->getOption(self::INPUT_KEY_LIMIT);
        }
        $searchDir = null;
        if ($input->getOption(self::INPUT_KEY_SEARCH_DIR)) {
            $basePath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $searchDir = $basePath . trim($input->getOption(self::INPUT_KEY_SEARCH_DIR), '/');
        }
        $startPath = null;
        if ($input->getOption(self::INPUT_KEY_START_PATH)) {
            $basePath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $startPath = $basePath . trim($input->getOption(self::INPUT_KEY_START_PATH), '/');
        }
        $this->progress = new ProgressBar($output, 50);
        $this->progress->setFormat('<comment>%message%</comment> %current% images');
        $this->progress->setMessage(__('Search images in file system'));
        $this->progress->start();
        $this->scanner->setCallback([$this, 'updateProgress']);
        if ($searchDir) {
            $this->scanner->prepareImagesFromDir($searchDir, $startPath, $limit);
        } else {
            $this->scanner->saveImageGalleryFiles($limit);
        }
        $output->writeln("");
        $this->progress = new ProgressBar($output, 50);
        $this->progress->setFormat('<comment>%message%</comment> %current% images');
        $this->progress->setMessage(__('Update images from database'));
        $this->progress->start();
        $this->scanner->updateImagesFromDatabase();
        while($this->cache->getFrontend()->test(ScannerManager::SCAN_DATABASE_STATUS_CACHE_KEY)) {
            $this->scanner->updateImagesFromDatabase();
        }
        $output->writeln("");
        $this->cache->remove(Config::SCAN_RUNNING_CACHE_KEY);
        return $this;
    }

    /**
     * @param int $callbackCount
     */
    public function updateProgress($callbackCount)
    {
        $this->progress->setProgress($callbackCount);
    }
}