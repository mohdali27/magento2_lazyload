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

namespace Webkul\Marketplace\Controller\Order\Shipment;

/**
 * Webkul Marketplace Order Shipment Email Controller.
 */
class Email extends \Webkul\Marketplace\Controller\Order
{
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $shipmentId = $this->getRequest()->getParam('shipment_id');
            if ($shipment = $this->_initShipment()) {
                try {
                    $this->_objectManager->create(
                        'Magento\Sales\Api\ShipmentManagementInterface'
                    )->notify($shipment->getEntityId());
                    $this->messageManager->addSuccess(
                        __('The message has been sent.')
                    );
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('Failed to send the Shipping email.')
                    );
                }

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/view',
                    [
                        'order_id' => $shipment->getOrder()->getId(),
                        'shipment_id' => $shipmentId,
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/history',
                    [
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
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
