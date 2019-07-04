<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse\Options;

/**
 * Country Options for Stores.
 *
 * @category Magestore
 * @package  Magestore_InventorySuccess
 * @module   Inventorysuccess
 * @author   Magestore Developer
 */
class Country implements \Magestore\InventorySuccess\Model\OptionManage\OptionInterface
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * Country constructor.
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
    ) {
        $this->_countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * Return array of options as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $option = [];
        /** @var \Magento\Directory\Model\ResourceModel\Country\Collection $collection */
        $collection = $this->_countryCollectionFactory->create()->loadByStore();

        foreach ($collection as $item) {
            $option[] = ['label' => $item->getName(), 'value' => $item->getId()];
        }

        return $option;
    }

    /**
     * Return array of options as key-value pairs.
     *
     * @return array Format: array('<key>' => '<value>', '<key>' => '<value>', ...)
     */
    public function toOptionHash()
    {
        $option = [];
        /** @var \Magento\Directory\Model\ResourceModel\Country\Collection $collection */
        $collection = $this->_countryCollectionFactory->create()->loadByStore();

        foreach ($collection as $item) {
            $option[$item->getId()] = $item->getName();
        }

        return $option;
    }
}
