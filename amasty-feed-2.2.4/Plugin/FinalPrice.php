<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Plugin;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\GroupedProduct\Pricing\Price\FinalPrice as MagentoFinalPrice;

class FinalPrice
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    public function aroundGetValue(MagentoFinalPrice $subject, callable $proceed)
    {
        $version = $this->productMetadata->getVersion();
        if ($version <= '2.1.9') {
            return $this->getValueNew($subject);
        }

        return $proceed();
    }

    private function getValueNew($subject)
    {
        /** @var MagentoFinalPrice $subject */
        $minProduct = $subject->getMinProduct();
        return $minProduct ?
            $minProduct->getPriceInfo()->getPrice(MagentoFinalPrice::PRICE_CODE)->getValue() :
            0.00;
    }
}