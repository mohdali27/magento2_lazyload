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
namespace Webkul\Marketplace\Plugin\CatalogSearch\Model\Adapter\Aggregation\Checker\Query;

class CatalogView
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->requestInterface = $requestInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function aroundIsApplicable(
        \Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView $subject,
        callable $proceed,
        \Magento\Framework\Search\RequestInterface $request
    ) {
        $action = $this->requestInterface->getFullActionName();
        if ($action == 'marketplace_seller_collection' || $action == 'rentalsystem_index_properties' || $action == 'sellersubdomain_collection_index') {
            $result = true;
            return $result;
        }
        return $proceed($request);
    }
}
