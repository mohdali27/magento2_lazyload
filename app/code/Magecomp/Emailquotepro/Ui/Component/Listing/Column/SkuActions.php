<?php

namespace Magecomp\Emailquotepro\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class SkuActions extends Column
{
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []

    )
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource( array $dataSource )
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $myArray = explode(',', $item[$fieldName]);
                $html = "";
                for ($i = 0; $i < count($myArray); $i++) {
                    $html .= "<div>" . $myArray[$i] . "</div>";
                }
                $item[$fieldName] = html_entity_decode($html);
            }
        }
        return $dataSource;
    }
}
