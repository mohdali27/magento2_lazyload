<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOrderNumber
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOrderNumber\Cron;

use Bss\CustomOrderNumber\Model\Config\Source\Frequency;

class Sequence 
{
    /**
     * Sequence
     *
     * @var Sequence
     */
    protected $sequence;

    /**
     * StoreManagerInterface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
     */
    public function __construct (
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
    ) {
        $this->storeManager = $storeManager;
        $this->sequence = $sequence;
    }

    /**
     * Cron Daily
     *
     * @return $this
     */
    public function cronDaily() 
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getStoreId();
            $this->sequence->setCron($storeId, Frequency::CRON_DAILY);
        }
        return $this;
    }

    /**
     * Cron Weekly
     *
     * @return $this
     */
    public function cronWeekly() 
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getStoreId();
            $this->sequence->setCron($storeId, Frequency::CRON_WEEKLY);
        }
        return $this;
    }

    /**
     * Cron Monthly
     *
     * @return $this
     */
    public function cronMonthly() 
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getStoreId();
            $this->sequence->setCron($storeId, Frequency::CRON_MONTHLY);
        }
        return $this;
    }

    /**
     * Cron Yearly
     *
     * @return $this
     */
    public function cronYearly() 
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getStoreId();
            $this->sequence->setCron($storeId, Frequency::CRON_YEARLY);
        }
        return $this;
    }
}
