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
namespace Bss\CustomOrderNumber\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreditmemoObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\CustomOrderNumber\Helper\Data
     */
    protected $helper;

    /**
     * Creditmemo Interface
     *
     * @var \Magento\Sales\Api\Data\CreditmemoInterface
     */
    protected $creditmemo;

    /**
     * Sequence
     *
     * @var \Bss\CustomOrderNumber\Model\ResourceModel\Sequence
     */
    protected $sequence;

    /**
     * Construct
     *
     * @param \Bss\CustomOrderNumber\Helper\Data $helper
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @param \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
     */
    public function __construct(
        \Bss\CustomOrderNumber\Helper\Data $helper,
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo,
        \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
    ) {
            $this->helper = $helper;
            $this->creditmemo = $creditmemo;
            $this->sequence = $sequence;
    }

    /**
     * Set Increment Id
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {   
        $creditmemoInstance = $observer->getCreditmemo();
        $storeId = $creditmemoInstance->getOrder()->getStoreId();
        if ($this->helper->isCreditmemoEnable($storeId)) {
            $entityType = 'creditmemo';
            if ($this->helper->isCreditmemoSameOrder($storeId)) {
                $orderIncrement = $creditmemoInstance->getOrder()->getIncrementId();
                $replace = $this->helper->getCreditmemoReplace($storeId);
                $replaceWith = $this->helper->getCreditmemoReplaceWith($storeId);
                $result = str_replace($replace, $replaceWith, $orderIncrement);
            } else {
                $format = $this->helper->getCreditmemoFormat($storeId);
                $startValue = $this->helper->getCreditmemoStart($storeId);
                $step = $this->helper->getCreditmemoIncrement($storeId);
                $padding = $this->helper->getCreditmemoPadding($storeId);            
                $pattern = "%0".$padding."d";

                if ($this->helper->isIndividualCreditmemoEnable($storeId)) {
                    if ($storeId == 1) {
                        $table = $this->sequence->getSequenceTable($entityType, '0');
                    } else {
                        $table = $this->sequence->getSequenceTable($entityType, $storeId);
                    }
                } else {
                    $table = $this->sequence->getSequenceTable($entityType, '0');
                }

                $counter = $this->sequence->counter($table, $startValue, $step, $pattern);
                $result = $this->sequence->replace($format, $storeId, $counter);
            }
            try {
                if (!empty($this->creditmemo->getCollection()->addAttributeToFilter('increment_id', $result)
                    ->getData('increment_id'))) {
                    $storeId = 1;
                    $extra = $this->sequence->extra($entityType, $storeId);
                    $result = $result.$extra;
                }
            } catch (\Exception $e) {
            }

            $creditmemoInstance->setIncrementId($result);
        }           
    }
}
