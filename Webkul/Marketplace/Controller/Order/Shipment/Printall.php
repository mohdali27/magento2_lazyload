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

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Webkul Marketplace Order Shipment Printall pdf Controller by date range.
 */
class Printall extends \Webkul\Marketplace\Controller\Order
{
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $get = $this->getRequest()->getParams();
            $todate = date_create($get['special_to_date']);
            $to = date_format($todate, 'Y-m-d H:i:s');
            $fromdate = date_create($get['special_from_date']);
            $from = date_format($fromdate, 'Y-m-d H:i:s');

            $shipmentIds = [];
            try {
                /* Get all orders of seller in selected date range. */
                $sellerId = $this->_customerSession->getCustomerId();
                $collection = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleslist'
                )
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                )
                ->addFieldToFilter(
                    'created_at',
                    ['datetime' => true, 'from' => $from, 'to' => $to]
                )
                ->addFieldToSelect('order_id')
                ->distinct(true);
                /* Get all shippingids of seller in selected date range. */
                $shippingColl = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Orders'
                )
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    ['in'=>$collection->getData()]
                )
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                )
                ->addFieldToSelect(
                    'shipment_id'
                );
                $shipmentIds = $shippingColl->getData();
                if (!empty($shipmentIds)) {
                    $shipments = $this->_objectManager->create(
                        'Magento\Sales\Model\ResourceModel\Order\Shipment\Collection'
                    )
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter(
                        'entity_id',
                        ['in' => $shipmentIds]
                    )
                    ->load();

                    if (!$shipments->getSize()) {
                        $this->messageManager->addError(
                            __('There are no printable documents related to selected date range.')
                        );

                        return $this->resultRedirectFactory->create()->setPath(
                            'marketplace/order/history',
                            [
                                '_secure' => $this->getRequest()->isSecure(),
                            ]
                        );
                    }
                    $pdf = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Order\Pdf\Shipment'
                    )->getPdf($shipments);
                    $date = $this->_objectManager->get(
                        'Magento\Framework\Stdlib\DateTime\DateTime'
                    )->date('Y-m-d_H-i-s');

                    return $this->_objectManager->get(
                        'Magento\Framework\App\Response\Http\FileFactory'
                    )->create(
                        'packingslip'.$date.'.pdf',
                        $pdf->render(),
                        DirectoryList::VAR_DIR,
                        'application/pdf'
                    );
                } else {
                    $this->messageManager->addError(
                        __('There are no printable documents related to selected date range.')
                    );

                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/order/history',
                        [
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/order/history',
                    [
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addError(
                    __('We can\'t print the shipment right now.')
                );

                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/order/history',
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
