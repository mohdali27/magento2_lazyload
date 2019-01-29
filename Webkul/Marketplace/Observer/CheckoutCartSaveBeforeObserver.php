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

namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Message\ManagerInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Webkul Marketplace CheckoutCartSaveBeforeObserver Observer.
 */
class CheckoutCartSaveBeforeObserver implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * @var MarketplaceHelperData
     */
    protected $_marketplaceHelperData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @param CheckoutSession                $checkoutSession
     * @param ManagerInterface               $messageManager
     * @param MarketplaceHelperData          $marketplaceHelperData
     * @param ProductRepositoryInterface     $productRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager,
        MarketplaceHelperData $marketplaceHelperData,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->_marketplaceHelperData = $marketplaceHelperData;
        $this->_productRepository = $productRepository;
    }

    /**
     * Checkout cart product add event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->_marketplaceHelperData->getAllowProductLimit()) {
                $items =  $this->_checkoutSession->getQuote()->getAllVisibleItems();
                foreach ($items as $item) {
                    $sellerProductDataColl = $this->_marketplaceHelperData->getSellerProductDataByProductId(
                        $item->getProductId()
                    );
                    if (count($sellerProductDataColl)) {
                        $product = $this->_productRepository->getById($item->getProductId());
                        $productTypeId = $product['type_id'];
                        if ($productTypeId != 'downloadable' && $productTypeId != 'virtual') {
                            $mpProductCartLimit = $product['mp_product_cart_limit'];
                            if (!$mpProductCartLimit) {
                                $mpProductCartLimit = $this->_marketplaceHelperData->getGlobalProductLimitQty();
                            }
                            if ($item->getQty() > $mpProductCartLimit) {
                                $item->setQty($mpProductCartLimit);
                                $productName = "<b>".$item->getName()."</b>";
                                $this->_messageManager->addError(
                                    __(
                                        'Sorry, but you can only add maximum %1 quantity of %2 in this cart.',
                                        $mpProductCartLimit,
                                        $productName
                                    )
                                );
                                $this->_checkoutSession->getQuote()->setHasError(true);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }
}
