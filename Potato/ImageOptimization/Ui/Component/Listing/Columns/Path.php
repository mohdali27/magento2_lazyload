<?php
namespace Potato\ImageOptimization\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Path extends \Magento\Ui\Component\Listing\Columns\Column
{
    /** @var Filesystem  */
    protected $filesystem;

    /**
     * Result constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context, UiComponentFactory $uiComponentFactory,
        Filesystem $filesystem,
        array $components = [], array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->filesystem = $filesystem;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $item['path'] = $this->getPath($item['path']);
        }

        return $dataSource;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getPath($path)
    {
        $basePath = rtrim($this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath(), '/');
        $path = str_replace($basePath, '', $path);
        return $path;
    }
}