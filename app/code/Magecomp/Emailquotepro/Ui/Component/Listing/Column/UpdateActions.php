<?php

namespace Magecomp\Emailquotepro\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class UpdateActions extends Column
{

    const BLOG_URL_PATH_EDIT = 'emailquotepro/emailproductquote/update';


    protected $urlBuilder;
    private $editUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::BLOG_URL_PATH_EDIT
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource( array $dataSource )
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['emailproductquote_id'])) {

                    $item[$name]['update'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['emailproductquote_id'], 'quote_id' => $item['quote_id'], 'customer_email' => $item['customer_email']]),
                        'label' => __('Update Quote')
                    ];


                }
            }
        }

        return $dataSource;
    }
}
