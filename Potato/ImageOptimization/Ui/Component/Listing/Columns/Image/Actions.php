<?php
namespace Potato\ImageOptimization\Ui\Component\Listing\Columns\Image;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class Actions
 */
class Actions extends Column
{
    /** @var UrlInterface  */
    protected $urlBuilder;

    /**
     * Actions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['optimize'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'po_image/image/optimize',
                        ['id' => $item['id']]
                    ),
                    'label' => __('Optimize'),
                    'hidden' => false,
                ];
                $item[$this->getData('name')]['restore'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'po_image/image/restore',
                        ['id' => $item['id']]
                    ),
                    'label' => __('Restore'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
