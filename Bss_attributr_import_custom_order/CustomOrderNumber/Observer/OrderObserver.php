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

class OrderObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\CustomOrderNumber\Helper\Data
     */
    protected $helper;

    /**
     * StoreManager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $session;

    /**
     * Order Interface
     *
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Session\Quote $session
     * @param \Magento\Sales\Api\Data\OrderInterface $order 
     * @param \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
     */
    public function __construct(
        \Bss\CustomOrderNumber\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Session\Quote $session,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
    ) {
            $this->helper = $helper;
            $this->storeManager = $storeManager;
            $this->session = $session;
            $this->sequence = $sequence;
            $this->order = $order;
    }

    /**
     * Set Increment Id
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {   
        if ($this->helper->isOrderEnable()) {
            $storeId = $this->storeManager->getStore()->getStoreId();
            $sessionId = $this->session->getStoreId();
            if (isset($sessionId)) {
                $storeId = $sessionId;
            }
            $format = $this->helper->getOrderFormat($storeId);
            $startValue = $this->helper->getOrderStart($storeId);
            $step = $this->helper->getOrderIncrement($storeId);
            $padding = $this->helper->getOrderPadding($storeId);
            $pattern = "%0".$padding."d";
            $entityType = 'order';
            if ($this->helper->isIndividualOrderEnable($storeId)) {
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
            try {
                if ($this->order->loadByIncrementId($result)->getId() !== null) {
                    $storeId = 1;
                    $extra = $this->sequence->extra($entityType, $storeId);
                    $result = $result.$extra;
                }
            } catch (\Exception $e) {
            }
            $orderInstance = $observer->getOrder();
            $orderInstance->setIncrementId($result);
        }           
    }
}
