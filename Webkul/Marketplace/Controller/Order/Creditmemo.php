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

namespace Webkul\Marketplace\Controller\Order;

/**
 * Webkul Marketplace Order Creditmemo Controller.
 */
class Creditmemo extends \Webkul\Marketplace\Controller\Order
{
    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return $this|bool
     */
    protected function _initCreditmemoInvoice($order)
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = $this->_invoiceRepository->get($invoiceId);
            $invoice->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }

        return false;
    }

    /**
     * Initialize creditmemo model instance.
     *
     * @return \Magento\Sales\Model\Order\Creditmemo|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _initOrderCreditmemo($order)
    {
        $data = $this->getRequest()->getPost('creditmemo');

        $creditmemo = false;

        $sellerId = $this->_customerSession->getCustomerId();
        $orderId = $order->getId();

        $invoice = $this->_initCreditmemoInvoice($order);
        $items = [];
        $itemsarray = [];
        $shippingAmount = 0;
        $codcharges = 0;
        $paymentCode = '';
        $paymentMethod = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
        }
        $trackingsdata = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        );
        foreach ($trackingsdata as $tracking) {
            $shippingAmount = $tracking->getShippingCharges();
            if ($paymentCode == 'mpcashondelivery') {
                $codcharges = $tracking->getCodCharges();
            }
        }
        if (isset($data['shipping_amount'])) {
            $data['shipping_amount'] = $shippingAmount;
        }
        $this->getRequest()->setPostValue('creditmemo', $data);
        $refundData = $this->getRequest()->getParams();
        $codCharges = 0;
        $tax = 0;
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        );
        foreach ($collection as $saleproduct) {
            if ($paymentCode == 'mpcashondelivery') {
                $codCharges = $codCharges + $saleproduct->getCodCharges();
            }
            $tax = $tax + $saleproduct->getTotalTax();
            array_push($items, $saleproduct['order_item_id']);
        }

        $savedData = $this->_getItemData($order, $items);

        $qtys = [];
        foreach ($savedData as $orderItemId => $itemData) {
            if (isset($itemData['qty']) && $itemData['qty']) {
                $qtys[$orderItemId] = $itemData['qty'];
            }
            if (isset($refundData['creditmemo']['items'][$orderItemId]['back_to_stock'])) {
                $backToStock[$orderItemId] = true;
            }
        }

        if (empty($refundData['creditmemo']['shipping_amount'])) {
            $refundData['creditmemo']['shipping_amount'] = 0;
        }
        if (empty($refundData['creditmemo']['adjustment_positive'])) {
            $refundData['creditmemo']['adjustment_positive'] = 0;
        }
        if (empty($refundData['creditmemo']['adjustment_negative'])) {
            $refundData['creditmemo']['adjustment_negative'] = 0;
        }
        if (!$shippingAmount >= $refundData['creditmemo']['shipping_amount']) {
            $refundData['creditmemo']['shipping_amount'] = 0;
        }
        $refundData['creditmemo']['qtys'] = $qtys;

        if ($invoice) {
            $creditmemo = $this->_creditmemoFactory->createByInvoice(
                $invoice,
                $refundData['creditmemo']
            );
        } else {
            $creditmemo = $this->_creditmemoFactory->createByOrder(
                $order,
                $refundData['creditmemo']
            );
        }

        /*
         * Process back to stock flags
         */
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            $orderItem = $creditmemoItem->getOrderItem();
            $parentId = $orderItem->getParentItemId();
            if (isset($backToStock[$orderItem->getId()])) {
                $creditmemoItem->setBackToStock(true);
            } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                $creditmemoItem->setBackToStock(true);
            } elseif (empty($savedData)) {
                $creditmemoItem->setBackToStock(
                    $this->_stockConfiguration->isAutoReturnEnabled()
                );
            } else {
                $creditmemoItem->setBackToStock(false);
            }
        }

        $this->_coreRegistry->register('current_creditmemo', $creditmemo);

        return $creditmemo;
    }

    /**
     * Save creditmemo.
     */
    public function execute()
    {
        $helper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $orderId = $this->getRequest()->getParam('id');
            $sellerId = $this->_customerSession->getCustomerId();
            if ($order = $this->_initOrder()) {
                try {
                    $creditmemo = $this->_initOrderCreditmemo($order);
                    if ($creditmemo) {
                        if (!$creditmemo->isValidGrandTotal()) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('The credit memo\'s total must be positive.')
                            );
                        }
                        $data = $this->getRequest()->getParam('creditmemo');

                        if (!empty($data['comment_text'])) {
                            $creditmemo->addComment(
                                $data['comment_text'],
                                isset($data['comment_customer_notify']),
                                isset($data['is_visible_on_front'])
                            );
                            $creditmemo->setCustomerNote($data['comment_text']);
                            $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                        }

                        if (isset($data['do_offline'])) {
                            //do not allow online refund for Refund to Store Credit
                            if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                                throw new \Magento\Framework\Exception\LocalizedException(
                                    __('Cannot create online refund for Refund to Store Credit.')
                                );
                            }
                        }
                        $creditmemoManagement = $this->_objectManager->create(
                            'Magento\Sales\Api\CreditmemoManagementInterface'
                        );
                        $creditmemo = $creditmemoManagement
                        ->refund($creditmemo, (bool) $data['do_offline'], !empty($data['send_email']));

                        /*update records*/
                        $creditmemoIds = [];
                        $trackingcol1 = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            ['eq' => $orderId]
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $sellerId]
                        );
                        foreach ($trackingcol1 as $tracking) {
                            if ($tracking->getCreditmemoId()) {
                                $creditmemoIds = explode(',', $tracking->getCreditmemoId());
                            }
                            $creditmemoId = $creditmemo->getId();
                            if ($creditmemoId && !in_array($creditmemoId, $creditmemoIds)) {
                                array_push($creditmemoIds, $creditmemo->getId());
                                $tracking->setCreditmemoId(implode(',', $creditmemoIds));
                                $tracking->save();
                            }
                        }

                        if (!empty($data['send_email'])) {
                            $this->_creditmemoSender->send($creditmemo);
                        }

                        if (!empty($data['send_email'])) {
                            $this->_creditmemoSender->send($creditmemo);
                        }

                        $this->messageManager->addSuccess(__('You created the credit memo.'));
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    $this->messageManager->addError(
                        __('We can\'t save the credit memo right now.').$e->getMessage()
                    );
                }

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/view',
                    [
                        'id' => $order->getEntityId(),
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
            } else {
                return $this->resultRedirectFactory->create()
                ->setPath(
                    '*/*/history',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Get requested items qtys.
     */
    protected function _getItemData($order, $items)
    {
        $refundData = $this->getRequest()->getParams();
        $data['items'] = [];
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)
                && isset($refundData['creditmemo']['items'][$item->getItemId()]['qty'])) {
                $data['items'][$item->getItemId()]['qty'] = intval(
                    $refundData['creditmemo']['items'][$item->getItemId()]['qty']
                );

                $_item = $item;
                // for bundle product
                $bundleitems = array_merge([$_item], $_item->getChildrenItems());
                if ($_item->getParentItem()) {
                    continue;
                }
            } else {
                if (!$item->getParentItemId()) {
                    $data['items'][$item->getItemId()]['qty'] = 0;
                }
            }
        }
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = [];
        }
        return $qtys;
    }
}
