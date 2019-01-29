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
namespace Webkul\Marketplace\Helper\Product;

use Magento\Framework\View\Result\Page as ResultPage;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends \Magento\Catalog\Helper\Product\View
{
    /**
     * @override_function
     */
    public function prepareAndRender(ResultPage $resultPage, $productId, $controller, $params = null)
    {
        /**
         * Remove default action handle from layout update to avoid its usage during processing of another action,
         * It is possible that forwarding to another action occurs, e.g. to 'noroute'.
         * Default action handle is restored just before the end of current method.
         */
        $defaultActionHandle = $resultPage->getDefaultLayoutHandle();
        $handles = $resultPage->getLayout()->getUpdate()->getHandles();
        if (in_array($defaultActionHandle, $handles)) {
            $resultPage->getLayout()->getUpdate()->removeHandle($resultPage->getDefaultLayoutHandle());
        }

        if (!$controller instanceof \Magento\Catalog\Controller\Product\View\ViewInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Bad controller interface for showing product')
            );
        }
        // Prepare data
        $mpproductHelper = $this->_catalogProduct;

        if (!$params) {
            $params = new \Magento\Framework\DataObject();
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mpproductHelper =  $objectManager->create("Webkul\Marketplace\Helper\Product");
        // Standard algorithm to prepare and render product view page
        $product = $mpproductHelper->initProduct($productId, $controller, $params);
        if (!$product) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Product is not loaded'));
        }
        $buyRequest = $params->getBuyRequest();
        if ($buyRequest) {
            $mpproductHelper->prepareProductOptions($product, $buyRequest);
        }
        if ($params->hasConfigureMode()) {
            $product->setConfigureMode($params->getConfigureMode());
        }
        $this->_eventManager->dispatch('catalog_controller_product_view', ['product' => $product]);
        $this->_catalogSession->setLastViewedProductId($product->getId());
        if (in_array($defaultActionHandle, $handles)) {
            $resultPage->addDefaultHandle();
        }
        $this->initProductLayout($resultPage, $product, $params);
        return $this;
    }
}
