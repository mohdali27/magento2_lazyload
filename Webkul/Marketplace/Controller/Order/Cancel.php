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

class Cancel extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Default customer account page.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            if ($order = $this->_initOrder()) {
                try {
                    $sellerId = $this->_customerSession->getCustomerId();
                    $flag = $this->_objectManager->create(
                        'Webkul\Marketplace\Helper\Orders'
                    )->cancelorder($order, $sellerId);
                    if ($flag) {
                        $paidCanceledStatus = \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_CANCELED;
                        $paymentCode = '';
                        $paymentMethod = '';
                        if ($order->getPayment()) {
                            $paymentCode = $order->getPayment()->getMethod();
                        }
                        $orderId = $this->getRequest()->getParam('id');
                        $collection = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Saleslist'
                        )
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            ['eq' => $orderId]
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $sellerId]
                        );
                        foreach ($collection as $saleproduct) {
                            $saleproduct->setCpprostatus(
                                $paidCanceledStatus
                            );
                            $saleproduct->setPaidStatus(
                                $paidCanceledStatus
                            );
                            if ($paymentCode == 'mpcashondelivery') {
                                $saleproduct->setCollectCodStatus(
                                    $paidCanceledStatus
                                );
                                $saleproduct->setAdminPayStatus(
                                    $paidCanceledStatus
                                );
                            }
                            $saleproduct->save();
                        }
                        $trackingcoll = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Orders'
                        )
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                        foreach ($trackingcoll as $tracking) {
                            $tracking->setTrackingNumber('canceled');
                            $tracking->setCarrierName('canceled');
                            $tracking->setIsCanceled(1);
                            $tracking->setOrderStatus('canceled');
                            $tracking->save();
                        }
                        $this->messageManager->addSuccess(
                            __('The order has been cancelled.')
                        );
                    } else {
                        $this->messageManager->addError(
                            __('You are not permitted to cancel this order.')
                        );

                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/history',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('We can\'t send the email order right now.')
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
                return $this->resultRedirectFactory->create()->setPath(
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
}
