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

namespace Webkul\Marketplace\Block\Adminhtml\Items\Column\Name;

class Seller extends \Magento\Sales\Block\Adminhtml\Items\Column\Name
{
    /**
     * Get Seller Name.
     *
     * @param string | $id
     * @param bool |   $flag
     *
     * @return array
     */
    public function getUserInfo($id, $flag)
    {
        $sellerId = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($flag == 1) {
            $assignProduct = $objectManager->get(
                'Webkul\MpAssignProduct\Model\Items'
            )->load($id);
            if ($assignProduct->getId() > 0) {
                $sellerId = $assignProduct->getSellerId();
            }
        } else {
            $marketplaceProductCollection = $objectManager->get(
                'Webkul\Marketplace\Model\Product'
            )
            ->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                ['eq' => $id]
            );
            if (count($marketplaceProductCollection)) {
                foreach ($marketplaceProductCollection as $product) {
                    $sellerId = $product->getSellerId();
                }
            }
        }
        if ($sellerId > 0) {
            $customer = $objectManager->get(
                'Magento\Customer\Model\Customer'
            )->load($sellerId);
            if ($customer) {
                $returnArray = [];
                $returnArray['name'] = $customer->getName();
                $returnArray['id'] = $sellerId;

                return $returnArray;
            }
        }
    }

    /**
     * Get Customer Url By Customer Id.
     *
     * @param string | $customerId
     *
     * @return string
     */
    public function getCustomerUrl($customerId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlbuilder = $objectManager->get(
            'Magento\Framework\UrlInterface'
        );

        return $urlbuilder->getUrl(
            'customer/index/edit',
            ['id' => $customerId]
        );
    }
}
