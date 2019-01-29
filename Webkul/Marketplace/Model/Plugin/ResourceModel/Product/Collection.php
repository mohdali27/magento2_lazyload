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
namespace Webkul\Marketplace\Model\Plugin\ResourceModel\Product;

class Collection
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->_registry = $registry;
    }

    /**
     * function to run to change the retun data of afterIsEnabledFlat.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param array $result
     *
     * @return bool
     */
    public function afterIsEnabledFlat(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        $result
    ) {
        if ($this->_registry->registry('mp_flat_catalog_flag')) {
            $result = 0;
        }
        return $result;
    }
}
