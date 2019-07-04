<?php
namespace Potato\ImageOptimization\Model\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Potato\ImageOptimization\Logger\Logger;
use Magento\Framework\App\CacheInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Manager\Image as ImageManager;
use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\App;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class Optimize
 */
class Optimize extends Command
{
    const INPUT_KEY_LIMIT = 'limit';
    
    const PROGRESS_FORMAT = '<comment>%message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%';
    
    /** @var Logger  */
    protected $logger;

    /** @var CacheInterface  */
    protected $cache;

    /** @var Config  */
    protected $config;

    /** @var ImageManager  */
    protected $imageManager;

    /** @var ImageRepositoryInterface  */
    protected $imageRepository;

    /** @var App  */
    protected $app;

    /**
     * Optimize constructor.
     * @param Logger $logger
     * @param CacheInterface $cache
     * @param Config $config
     * @param ImageManager $imageManager
     * @param ImageRepositoryInterface $imageRepository
     * @param App $app
     * @param null $name
     */
    public function __construct(
        Logger $logger,
        CacheInterface $cache,
        Config $config,
        ImageManager $imageManager,
        ImageRepositoryInterface $imageRepository,
        App $app,
        $name = null
    ) {
        parent::__construct($name);
        $this->logger = $logger;
        $this->cache = $cache;
        $this->imageRepository = $imageRepository;
        $this->imageManager = $imageManager;
        $this->config = $config;
        $this->app = $app;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('po_image_optimization:optimize')
            ->setDefinition([
                new InputOption(
                    self::INPUT_KEY_LIMIT,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Optimize until optimized image count < limit'
                )
            ])
            ->setDescription('Potato Image Optimizer: manually optimize via console');

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
        if (true === $this->cache->getFrontend()->test(Config::OPTIMIZATION_RUNNING_CACHE_KEY)) {
            $output->writeln('Optimization is already running. Clean cache if you want run optimization again');
            return $this;
        }
        $this->cache->save(true, Config::OPTIMIZATION_RUNNING_CACHE_KEY);
        /* optimize via lib */
        $limit = $this->imageRepository->getListByStatus(StatusSource::STATUS_PENDING, true) +
            $this->imageRepository->getListByStatus(StatusSource::STATUS_OUTDATED, true);
        if ($input->getOption(self::INPUT_KEY_LIMIT) && $input->getOption(self::INPUT_KEY_LIMIT) < $limit) {
            $limit = $input->getOption(self::INPUT_KEY_LIMIT);
        }
        $progress = new ProgressBar($output, $limit);
        $progress->setFormat(self::PROGRESS_FORMAT);
        $progress->setMessage("Optimize images");
        $count = 0;
        while ($count < $limit) {
            $imageCollection = $this->imageRepository->getNeedToOptimizationList();
            $this->imageManager->optimizeImageCollection($imageCollection->getItems());
            $count += count($imageCollection->getItems());
            $progress->advance(count($imageCollection->getItems()));
        }
        $output->writeln("");
        
        /* optimize via service */
        $limit = $this->imageRepository->getListByStatus(StatusSource::STATUS_PENDING_SERVICE, true);
        if ($input->getOption(self::INPUT_KEY_LIMIT) && $input->getOption(self::INPUT_KEY_LIMIT) < $limit) {
            $limit = $input->getOption(self::INPUT_KEY_LIMIT);
        }
        $progress = new ProgressBar($output, $limit);
        $progress->setFormat(self::PROGRESS_FORMAT);
        $progress->setMessage("Transfer to the service");
        $count = 0;
        while ($count < $limit) {
            $sentImagesCount = $this->app->prepareAndSendServiceImages();
            $count += $sentImagesCount;
            $progress->advance($sentImagesCount);
        }
        $output->writeln("");
        $this->cache->remove(Config::OPTIMIZATION_RUNNING_CACHE_KEY);
        return $this;
    }
}