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
 * Webkul Marketplace Sold Product Order Details Controller.
 */
class Salesdetail extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Webkul Marketplace Sold Product Order Details page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            /* @var \Magento\Framework\View\Result\Page $resultPage */

            $productId = (int) $this->getRequest()->getParam('id');

            $resultPage = $this->_resultPageFactory->create();
            if ($helper->getIsSeparatePanel()) {
                $resultPage->addHandle('marketplace_layout2_order_salesdetail');
            }
            $resultPage->getConfig()->getTitle()->set(
                __(
                    'Order Details of Product : %1',
                    $this->_objectManager->create(
                        'Magento\Catalog\Model\Product'
                    )->load($productId)->getName()
                )
            );

            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
