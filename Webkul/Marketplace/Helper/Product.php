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
namespace Webkul\Marketplace\Helper;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Catalog category helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends \Magento\Catalog\Helper\Product
{
    /**
     * @override_function
     */
    public function initProduct($productId, $controller, $params = null)
    {
        // Prepare data for routine
        if (!$params) {
            $params = new \Magento\Framework\DataObject();
        }
        // Init and load product
        $this->_eventManager->dispatch(
            'catalog_controller_product_init_before',
            ['controller_action' => $controller, 'params' => $params]
        );
        if (!$productId) {
            return false;
        }
        try {
            $catalogProduct = $this->productRepository->getById($productId, false, $this->_storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
        if (!in_array($this->_storeManager->getStore()->getWebsiteId(), $catalogProduct->getWebsiteIds())) {
            return false;
        }
        // Load product current category
        $categoryId = $params->getCategoryId();
        if (!$categoryId && $categoryId !== false) {
            $lastId = $this->_catalogSession->getLastVisitedCategoryId();
            if ($catalogProduct->canBeShowInCategory($lastId)) {
                $categoryId = $lastId;
            }
        } elseif (!$catalogProduct->canBeShowInCategory($categoryId)) {
            $categoryId = null;
        }
        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $e) {
                $category = null;
            }
            if ($category) {
                $catalogProduct->setCategory($category);
                $this->_coreRegistry->register('current_category', $category);
            }
        }
        $this->_coreRegistry->register('current_product', $catalogProduct);
        $this->_coreRegistry->register('product', $catalogProduct);
        try {
            $this->_eventManager->dispatch(
                'catalog_controller_product_init_after',
                ['product' => $catalogProduct, 'controller_action' => $controller]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return false;
        }
        return $catalogProduct;
    }
}
