<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @param ContextInterface                $context
     * @param UiComponentFactory              $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image   $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array                           $components
     * @param array                           $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $store = $this->storeManager->getStore(
            $this->context->getFilterParam(
                'store_id',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            )
        );
        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $product = $this->_objectManager->create(
                    'Magento\Catalog\Model\Product'
                )->load($item['mageproduct_id']);
                //$product = new \Magento\Framework\DataObject($item);
                $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail');
                $imageUrl = $imageHelper->getUrl();
                $item[$fieldName.'_src'] = $imageUrl;
                $item[$fieldName.'_alt'] = $imageHelper->getLabel();
                $origImageHelper = $this->imageHelper->init(
                    $product,
                    'product_listing_thumbnail_preview'
                );
                $item[$fieldName.'_orig_src'] = $origImageHelper->getUrl();
                $item[$fieldName.'_name'] = $product->getName();
                if ($product->getPrice() * 1) {
                    $price = $product->getFormatedPrice();
                } else {
                    $price = $currency->toCurrency(sprintf('%f', $product->getPrice()));
                }
                $item[$fieldName.'_price'] = __('Price').' - '.strip_tags(html_entity_decode($price));
                $item[$fieldName.'_description'] = strip_tags(
                    html_entity_decode($product->getDescription())
                );
                $item[$fieldName.'_link'] = $this->urlBuilder->getUrl(
                    'catalog/product/edit',
                    ['id' => $item['mageproduct_id'], 'store' => 0]
                );
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;

        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
