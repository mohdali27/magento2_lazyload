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

namespace Webkul\Marketplace\Block\Adminhtml\Customer;

use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;

class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var null
     */
    protected $_objectManager = null;
    /**
     * @var CountryCollection
     */
    protected $_country;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @param \Magento\Backend\Block\Widget\Context     $context
     * @param \Magento\Framework\Registry               $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param CountryCollection                         $country
     * @param \Magento\Directory\Model\Currency         $currency
     * @param \Webkul\Marketplace\Helper\Data           $helper
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CountryCollection $country,
        \Magento\Directory\Model\Currency $currency,
        \Webkul\Marketplace\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_country = $country;
        $this->_currency = $currency;
        parent::__construct($context, $data);
    }

    public function getSellerInfoCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $requestParams = $this->getRequest()->getParams();
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $data = [];
        if ($customerId != '') {
            $user = $this->_objectManager->get(
                'Magento\Customer\Model\Customer'
            )->load($customerId);
            if (!isset($requestParams['store'])) {
                $storeId = $user->getStoreId();
            }
            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Seller'
            )->getCollection()
            ->addFieldToFilter('seller_id', $customerId)
            ->addFieldToFilter('store_id', $storeId);
            if (!count($collection)) {
                $collection = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                )->getCollection()
                ->addFieldToFilter('seller_id', $customerId)
                ->addFieldToFilter('store_id', 0);
            }
            $name = explode(' ', $user->getName());
            foreach ($collection as $record) {
                $data = $record->getData();
                $bannerpic = $record->getBannerPic();
                $logopic = $record->getLogoPic();
                $countrylogopic = $record->getCountryPic();
                if (strlen($bannerpic) <= 0) {
                    $bannerpic = 'banner-image.png';
                }
                if (strlen($logopic) <= 0) {
                    $logopic = 'noimage.png';
                }
                if (strlen($countrylogopic) <= 0) {
                    $countrylogopic = '';
                }
            }
            $data['firstname'] = $name[0];
            $data['lastname'] = $name[1];
            $data['email'] = $user->getEmail();
            $data['banner_pic'] = $bannerpic;
            $data['logo_pic'] = $logopic;
            $data['country_pic'] = $countrylogopic;

            return $data;
        }
    }
    /**
     * @return Mixed.
     */
    public function getCountryList()
    {
        return $this->_country->loadByStore()->toOptionArray(true);
    }
    public function getPaymentMode()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Seller'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);
        $data = '';
        foreach ($collection as $record) {
            $data = $record->getPaymentSource();
        }

        return $data;
    }

    /**
     * @return Webkul\Marketplace\Model\Saleperpartner
     */
    public function getSalesPartnerCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleperpartner'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }
    /**
     * @return Webkul\Marketplace\Model\Saleslist
     */
    public function getSalesListCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }
    /**
     * @return string
     */
    public function getConfigCommissionRate()
    {
        return $this->_helper->getConfigCommissionRate();
    }
    /**
     * @param Decimal $price
     *
     * @return [type] [description]
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }
    /**
     * @return Webkul\Marketplace\Model\Product
     */
    public function getProductCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId)
        ->addFieldToFilter('adminassign', 1);

        return $collection;
    }
    /**
     * @return Webkul\Marketplace\Model\Seller
     */
    public function getMarketplaceUserCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Seller'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }

    public function getAllCustomerCollection()
    {
        $collection = $this->_objectManager->create(
            'Magento\Customer\Model\Customer'
        )->getCollection();

        return $collection;
    }
}
