<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product as ExportProduct;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class Taxpercents implements RowCustomizerInterface
{
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    private $calculation;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Feed\Model\Export\Product
     */
    private $export;

    /**
     * @var array
     */
    private $taxes = [];

    public function __construct(
        ExportProduct $export,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        $this->export = $export;
        $this->calculation = $calculation;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        $prefixOtherAttributes = ExportProduct::PREFIX_OTHER_ATTRIBUTES;
        if ($this->export->hasAttributes($prefixOtherAttributes)) {
            $collection->applyFrontendPriceLimitations();
            $storeId = $this->request->getParam('store_id');

            foreach ($collection as &$item) {
                $addressRequestObject
                    = $this->calculation->getDefaultRateRequest($storeId);
                $addressRequestObject->setProductClassId(
                    $item->getTaxClassId()
                );

                $taxPercent = $this->calculation->getRate(
                    $addressRequestObject
                );

                if (isset($item['entity_id'])) {
                    $this->taxes[$item['entity_id']] = $taxPercent;
                }
            }
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

        $taxPercent = "0";
        if (isset($this->taxes[$productId]) && $this->taxes[$productId]) {
            $notForamttedTaxpercent = $this->taxes[$productId];
            $taxPercent = sprintf("%0.2f", $notForamttedTaxpercent);
        }

        $customData[ExportProduct::PREFIX_OTHER_ATTRIBUTES]['tax_percents']
            = (string)$taxPercent;

        gc_collect_cycles();

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
