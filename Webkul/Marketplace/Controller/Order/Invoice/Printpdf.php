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

namespace Webkul\Marketplace\Controller\Order\Invoice;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Webkul Marketplace Order Invoice PDF Print Controller.
 */
class Printpdf extends \Webkul\Marketplace\Controller\Order
{
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            if ($invoice = $this->_initInvoice()) {
                try {
                    $pdf = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Order\Pdf\Invoice'
                    )->getPdf(
                        [$invoice]
                    );
                    $date = $this->_objectManager->get(
                        'Magento\Framework\Stdlib\DateTime\DateTime'
                    )->date('Y-m-d_H-i-s');
                    return $this->_objectManager->get(
                        'Magento\Framework\App\Response\Http\FileFactory'
                    )->create(
                        'invoice' . $date . '.pdf',
                        $pdf->render(),
                        DirectoryList::VAR_DIR,
                        'application/pdf'
                    );
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/order/history',
                        [
                            '_secure'=>$this->getRequest()->isSecure()
                        ]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->messageManager->addError(
                        __('We can\'t print the invoice right now.')
                    );
                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/order/history',
                        [
                            '_secure'=>$this->getRequest()->isSecure()
                        ]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/order/history',
                    [
                        '_secure'=>$this->getRequest()->isSecure()
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
