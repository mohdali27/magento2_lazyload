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
namespace Webkul\Marketplace\Model\Notification;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class MarketplaceConfigProvider
{

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $authSession;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var UrlInterface
     */
    protected $helper;

    /**
     * View file system
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

     /**
      * @var \Magento\Framework\Stdlib\DateTime\DateTime
      */
    protected $date;

    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        FormKey $formKey,
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        \Webkul\Marketplace\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\View\Asset\Repository $viewFileSystem,
        \Magento\Backend\Helper\Data $adminHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->authSession = $authSession;
        $this->formKey = $formKey;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->adminHelper = $adminHelper;
        $this->orderHelper = $orderHelper;
        $this->viewFileSystem = $viewFileSystem;
        $this->objectManager = $objectManager;
        $this->date = $date;
    }
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        if ($this->isAdminLoggedIn()) {
            $defaultImageUrl = $this->viewFileSystem->getUrlWithParams(
                'Webkul_Marketplace::images/icons_notifications.png',
                []
            );
            $output['formKey'] = $this->formKey->getFormKey();
            $output['image'] = $defaultImageUrl;
            $output['productNotification'] = $this->getProductNotificationData();
            $output['sellerNotification'] = $this->getSellerNotificationData();
            $output['feedbackNotification'] = $this->getFeedbackNotificationData();
        }
        return $output;
    }

    /**
     * return seller product data for notification.
     * @return array
     */
    protected function getProductNotificationData()
    {
        $productData = [];
        $markeplaceProduct = $this->objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )->getCollection()
        ->addFieldToFilter('admin_pending_notification', ['neq' => 0]);

        if ($markeplaceProduct->getSize()) {
            foreach ($markeplaceProduct as $value) {
                $title = '';
                $desc = '';
                if ($value->getAdminPendingNotification() == 1) {
                    $title = __('New product added by the seller');
                    $desc = __(
                        sprintf(
                            'Product "%s" added by the seller "%s", please check Marketplace product list.',
                            '<span class="wk-focus">'.$this->getProductName($value->getMageproductId()).'</span>',
                            '<span class="wk-focus">'.$this->getSellerName($value->getSellerId())->getName().'</span>'
                        )
                    );
                }
                if ($value->getAdminPendingNotification() == 2) {
                    $title = __('Product details updated by seller');
                    $desc = __(
                        sprintf(
                            'Product "%s" updated by the seller "%", please check Marketplace product list.',
                            '<span class="wk-focus">'.$this->getProductName($value->getMageproductId()).'</span>',
                            '<span class="wk-focus">'.$this->getSellerName($value->getSellerId())->getName().'</span>'
                        )
                    );
                }
                $productData[] = [
                    'product_id' => $value->getMageproductId(),
                    'title' => $title,
                    'desc'  => $desc,
                    'seller_id' => $value->getSellerId(),
                    'seller_name' => $this->getSellerName($value->getSellerId())->getName(),
                    'updated_time'  => $this->date->gmtDate(
                        'l jS \of F Y h:i:s A',
                        strtotime($value->getUpdatedAt())
                    ),
                    'url' => $this->adminHelper->getUrl('marketplace/product')
                ];
            }
        }

        return $productData;
    }

    /**
     * create newly created seller notification data.
     * @return array
     */
    protected function getSellerNotificationData()
    {
        $sellerData = [];
        $sellerCollection = $this->objectManager->create(
            'Webkul\Marketplace\Model\Seller'
        )->getCollection()
        ->addFieldToFilter('admin_notification', ['neq' => 0]);
        if ($sellerCollection->getSize()) {
            foreach ($sellerCollection as $seller) {
                $title = '';
                $desc = '';
                if ($seller->getAdminNotification() == 1) {
                    $title = __('New Customer registered as seller');
                    $desc = __(
                        sprintf(
                            'Customer "%s" requested to become seller, click here to see Marketplce Seller list.',
                            '<span class="wk-focus">'.$this->getSellerName($seller->getSellerId())->getName().'</span>'
                        )
                    );
                }
                $sellerData[] = [
                    'seller_id' => $seller->getSellerId(),
                    'title' => $title,
                    'desc'  => $desc,
                    'seller_name' => $this->getSellerName($seller->getSellerId())->getName(),
                    'updated_time'  => $this->date->gmtDate(
                        'l jS \of F Y h:i:s A',
                        strtotime($seller->getUpdatedAt())
                    ),
                    'url' => $this->adminHelper->getUrl('marketplace/seller')
                ];
            }
        }
        return $sellerData;
    }

    /**
     * create newly created seller notification data.
     * @return array
     */
    protected function getFeedbackNotificationData()
    {
        $feedBackData = [];
        $feedBackCollection = $this->objectManager->create(
            'Webkul\Marketplace\Model\Feedback'
        )->getCollection()
        ->addFieldToFilter('admin_notification', ['neq' => 0]);
        if ($feedBackCollection->getSize()) {
            foreach ($feedBackCollection as $feedback) {
                $title = '';
                $desc = '';
                if ($feedback->getAdminNotification() == 1) {
                    $title = __('New review submitted by buyer');
                    $desc = __(
                        sprintf(
                            'Buyer submitted review for seller "%s", click here to see the feedback list.',
                            '<span class="wk-focus">'.$this->getSellerName($feedback->getSellerId())->getName().'</span>'
                        )
                    );
                }
                $feedBackData[] = [
                    'seller_id' => $feedback->getSellerId(),
                    'title' => $title,
                    'desc'  => $desc,
                    'seller_name' => $this->getSellerName($feedback->getSellerId())->getName(),
                    'updated_time'  => $this->date->gmtDate(
                        'l jS \of F Y h:i:s A',
                        strtotime($feedback->getCreatedAt())
                    ),
                    'url' => $this->adminHelper->getUrl('marketplace/feedback')
                ];
            }
        }
        return $feedBackData;
    }

    /**
     * Product name
     * @param  int $productId
     * @return string
     */
    protected function getProductName($productId)
    {
        $product = $this->objectManager->create(
            'Magento\Catalog\Model\Product'
        )->load($productId);

        return $product->getName();
    }

    /**
     * load customer by id
     * @param  int $id
     * @return Magento\Customer\Model\Customer
     */
    protected function getSellerName($id)
    {
        $customer = $this->objectManager->create(
            'Magento\Customer\Model\Customer'
        )->load($id);
        return $customer;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     * @codeCoverageIgnore
     */
    private function isAdminLoggedIn()
    {
        return (bool)$this->authSession->isLoggedIn();
    }
}
