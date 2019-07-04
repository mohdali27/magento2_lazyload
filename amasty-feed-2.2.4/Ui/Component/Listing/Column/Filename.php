<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Ui\Component\Listing\Column;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Filename extends Column
{
    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlFactory $urlFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlFactory = $urlFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $item['generated_at']
                    ? $this->_getValue($item[$this->getData('name')], $item['orig_store_id']) : '';
            }
        }

        return $dataSource;
    }

    private function getUrlInstance()
    {
        return $this->urlFactory->create();
    }

    protected function _getValue($filename, $storeId)
    {
        $urlInstance = $this->getUrlInstance();

        $routeParams = [
            '_direct' => 'amfeed/feed/download',
            '_query'  => [
                'filename' => $filename
            ]
        ];

        $href = $urlInstance
            ->setScope($storeId)
            ->getUrl(
                '',
                $routeParams
            );

        return [
            'view' => [
                'href'  => $href,
                'label' => __('Download')
            ]
        ];
    }
}
