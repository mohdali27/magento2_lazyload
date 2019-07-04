<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product;
use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice as CatalogSpecialPrice;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Convert\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Calculation\CollectionFactory;

class Price implements RowCustomizerInterface
{
    protected $_prices = [];

    protected $_storeManager;

    protected $_export;

    protected $_calculationCollectionFactory;

    protected $_objectConverter;

    protected $_data;

    /**
     * @var Calculation
     */
    private $calculation;

    /**
     * @var Http
     */
    private $request;

    public function __construct(
        StoreManagerInterface $storeManager,
        Product $export,
        CollectionFactory $calculationCollectionFactory,
        Calculation $calculation,
        Http $request,
        DataObject $objectConverter
    ) {
        $this->_storeManager = $storeManager;
        $this->_export = $export;
        $this->_calculationCollectionFactory = $calculationCollectionFactory;
        $this->_objectConverter = $objectConverter;
        $this->calculation = $calculation;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->_export->hasAttributes(Product::PREFIX_PRICE_ATTRIBUTE)) {

            $collection->applyFrontendPriceLimitations();
            $collection->addAttributeToSelect(['special_price', 'special_from_date', 'special_to_date']);

            $storeId = $this->request->getParam('store_id') ?: $collection->getStoreId();

            $currentCurrency = $this->_storeManager->getStore()->getCurrentCurrency();
            $this->_storeManager->getStore()->setCurrentCurrency($this->_storeManager->getStore()->getBaseCurrency());

            foreach ($collection as &$item) {
                $addressRequestObject = $this->calculation->getDefaultRateRequest($storeId);
                $addressRequestObject->setProductClassId($item->getTaxClassId());

                $taxPercent = $this->calculation->getRate($addressRequestObject);
                $finalPrice = $item->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE)->getValue();

                if ($finalPrice === null) {
                    $item->load($item->getId());

                    if ($specialPrice = $item->getPriceInfo()->getPrice(CatalogSpecialPrice::PRICE_CODE)->getValue()) {
                        $finalPrice = $specialPrice;
                    } else {
                        $finalPrice = $item->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE)->getValue();
                    }
                }

                $this->_prices[$item['entity_id']] = [
                    'final_price' => $finalPrice,
                    'price' => $item['price'],
                    'min_price' => $item['min_price'],
                    'max_price' => $item['max_price'],
                    'tax_price' => $taxPercent != 0 ?
                        ($item['price'] + $item['price'] * $taxPercent / 100)
                        : $item['price'],
                    'tax_final_price' => $taxPercent != 0 ?
                        ($finalPrice + $finalPrice * $taxPercent / 100)
                        : $finalPrice
                ];
            }

            $this->_storeManager->getStore()->setCurrentCurrency($currentCurrency);
        }
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];

        $customData[Product::PREFIX_PRICE_ATTRIBUTE]
            = isset($this->_prices[$productId]) ? $this->_prices[$productId]
            : [];

        return $dataRow;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
