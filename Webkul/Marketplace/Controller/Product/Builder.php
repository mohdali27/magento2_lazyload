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

namespace Webkul\Marketplace\Controller\Product;

/**
 * Webkul Marketplace Product Builder Controller Class.
 */
class Builder
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry           $registry
     * @param \Psr\Log\LoggerInterface              $loggerInterface
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $registry,
        \Webkul\Marketplace\Helper\Data $helper,
        \Psr\Log\LoggerInterface $loggerInterface
    ) {
        $this->_productFactory = $productFactory;
        $this->_logger = $loggerInterface;
        $this->_helper = $helper;
        $this->_registry = $registry;
    }

    /**
     * Build product based on requestData.
     *
     * @param $requestData
     *
     * @return \Magento\Catalog\Model\Product $mageProduct
     */
    public function build($requestData, $store = 0)
    {
        if (!empty($requestData['id'])) {
            $mageProductId = (int) $requestData['id'];
        } else {
            $mageProductId = '';
        }
        /** @var $mageProduct \Magento\Catalog\Model\Product */
        $mageProduct = $this->_productFactory->create();
        if (!empty($requestData['set'])) {
            $mageProduct->setAttributeSetId($requestData['set']);
        }
        if (!empty($requestData['type'])) {
            $mageProduct->setTypeId($requestData['type']);
        }
        $mageProduct->setStoreId($store);
        if ($mageProductId) {
            try {
                $isPartner = $this->_helper->isSeller();
                $flag = false;
                if ($isPartner == 1) {
                    $rightseller = $this->_helper->isRightSeller($mageProductId);
                    if ($rightseller == 1) {
                        $flag = true;
                    }
                }
                if ($flag) {
                    $mageProduct->load($mageProductId);
                }
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
        if (!$this->_registry->registry('product')) {
            $this->_registry->register('product', $mageProduct);
        }
        if (!$this->_registry->registry('current_product')) {
            $this->_registry->register('current_product', $mageProduct);
        }
        return $mageProduct;
    }
}
