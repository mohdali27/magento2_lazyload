<?php
/**
 * Webkul MpCustom plugins.
 * @category  Webkul
 * @package   Webkul_MpCustom
 * @author    Webkul
 * @copyright Copyright (c) 2010-2019 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpCustom\Plugin;

class EditProfileAfterPlugin
{
    public function __construct(
      \Psr\Log\LoggerInterface $logger,
      \Webkul\Marketplace\Model\Seller $sellerModel,
      \Webkul\Marketplace\Helper\Data $helper,
      \Magento\Tax\Model\ClassModel $taxClass,
      \Webkul\Marketplace\Model\Product $marketplaceProduct,
      \Magento\Catalog\Model\Product $productModel
    ) {
        $this->logger = $logger;
        $this->sellerModel = $sellerModel;
        $this->helper = $helper;
        $this->taxClass = $taxClass;
        $this->marketplaceProduct = $marketplaceProduct;
        $this->productModel = $productModel;
    }

    /**
     * after plugin for save profile controller
     */
    public function afterExecute(\Webkul\Marketplace\Controller\Account\EditprofilePost $subject, $result)
    {
        $sellerId = $this->helper->getCustomerId();
        $storeId = $this->helper->getCurrentStoreId();
        $taxClassId = 0;

        //custom tax class id we created in InstallData
        $taxClassCollection = $this->taxClass->getCollection()->addFieldToFilter('class_name','Custom VAT');
        foreach ($taxClassCollection as $value) {
          $taxClassId = $value->getId();
        }

        //adding tax class to all the seller products
        $collection = $this->sellerModel->getCollection()->addFieldToFilter('seller_id', $sellerId)->addFieldToFilter('store_id', $storeId);
        $marCollec = $this->marketplaceProduct->getCollection()->addFieldToFilter('seller_id', $sellerId);
        foreach ($collection as $sellerData) {
            if($sellerData->getIsVat()) {
                //set every seller product tax class to custom tax class
                foreach ($marCollec as $value) {
                  $product = $this->productModel->load($value->getMageproductId());
                  $product->setStoreId(0)->setTaxClassId($taxClassId);
                  $product->save();
                }
            } else {
                //set every seller product tax class to none
                foreach ($marCollec as $value) {
                  $product = $this->productModel->load($value->getMageproductId());
                  $product->setStoreId(0)->setTaxClassId(0);
                  $product->save();
                }
            }
        }
        return $result;
    }

    /**
     * before plugin for save profile controller
     */
    public function beforeExecute(\Webkul\Marketplace\Controller\Account\EditprofilePost $subject)
    {
        $value = $subject->getRequest()->getParam('is_vat');
        if (!isset($value)) {
            $subject->getRequest()->setParam('is_vat',0);
        }
    }
}
