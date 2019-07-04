<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\LowStockNotification;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class Notification implements ObserverInterface
{

    /**
     *
     */
    const TIME_LIFE = 300;
    const CACHE_LOWSTOCK_ID = 'low_stock_notification';

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $this->notification();
    }

    /**
     * notification
     */
    public function notification()
    {
        /** @var \Magento\Framework\App\CacheInterface $cache */
        $cache = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\App\CacheInterface'
        );

        /**
         * Magento can execute cronjob multiple times in same php process.
         * It can cause problems with ntification, email ...
         * We need check cron to avoid this problems.
         * If the problems still happens - you can refer to use "lock" and "unlock table" solution
         */
        if ($this->cronIsRunning($cache)){
            return ;
        }
        $this->startCron($cache);

        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\RuleManagement $ruleManagement */
        $ruleManagement = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\RuleManagement'
        );
        $availableRules = $ruleManagement->getAvailableRules();
        if (count($availableRules)) {
            foreach ($availableRules as $rule) {
                $ruleManagement->startNotification($rule);
            }
        }

        if(!count($ruleManagement->getAvailableRules())){
            $this->stopCron($cache);
        }

    }

    /**
     * @param $cache
     */
    public function startCron($cache){
        $cacheId = self::CACHE_LOWSTOCK_ID;
        $timeout = self::TIME_LIFE;
        $cache->save('1', $cacheId, array($cacheId), $timeout);
    }

    /**
     * @param $cache
     * @return bool
     */
    public function cronIsRunning($cache){
        if($cache->load(self::CACHE_LOWSTOCK_ID)){
            return true;
        }
        return false;
    }

    /**
     * @param $cache
     */
    public function stopCron($cache){
        $cache->remove(self::CACHE_LOWSTOCK_ID);
    }
}