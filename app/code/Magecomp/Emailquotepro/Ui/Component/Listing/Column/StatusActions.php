<?php

namespace Magecomp\Emailquotepro\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class StatusActions extends Column
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
                if ($item[$fieldName] == '0') {
                    $html = '<button style="background-color:#FF8804;color:white;padding:10px;border-radius: 9px;text-align: center;width: 100%" >sent</button>';
                } elseif ($item[$fieldName] == '1') {
                    $html = '<button style="background-color:#16B769;color:white;padding:10px;border-radius: 9px;text-align: center;width: 100%" >Clicked</button>';
                } elseif ($item[$fieldName] == '2') {
                    $html = '<button style="background-color:#16B769;color:white;padding:10px;border-radius: 9px;text-align: center;width: 100%" >Clicked</button>';
                } else {
                    $html = '<button style="background-color:#a5a8bb;color:white;padding:10px;border-radius: 9px;text-align: center;width: 100%" >Ordered</button>';
                }
                $item[$fieldName] = html_entity_decode($html);
            }
        }
        return $dataSource;
    }
}
