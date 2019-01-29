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

namespace Webkul\Marketplace\Controller\Order\Creditmemo;

/**
 * Webkul Marketplace Order Creditmemo Email Controller.
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
            $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
            if ($creditmemo = $this->_initCreditmemo()) {
                try {
                    $this->_objectManager->create(
                        'Magento\Sales\Api\CreditmemoManagementInterface'
                    )->notify($creditmemo->getEntityId());
                    $this->messageManager->addSuccess(
                        __('The message has been sent.')
                    );
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('Failed to send the creditmemo email.')
                    );
                }

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/view',
                    [
                        'order_id' => $creditmemo->getOrder()->getId(),
                        'creditmemo_id' => $creditmemoId,
                        '_secure' => $this->getRequest()->isSecure()
                    ]
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/history',
                    [
                        '_secure' => $this->getRequest()->isSecure()
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
