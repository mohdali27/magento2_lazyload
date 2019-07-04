<?php


namespace Meetanshi\Notifications\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;

class Data extends AbstractHelper
{
    const ENABLE = 'admin_notifications/general/enable';
    const SENDER = 'admin_notifications/general/email_sender';
    const RECEIVER = 'admin_notifications/general/receiver';

    const ORDER_ENABLE = 'admin_notifications/general/order/enable';
    const ORDER_EMAIL_TEMPLATE = 'admin_notifications/general/order/email_template';

    const ORDER_STATUS_ENABLE = 'admin_notifications/general/order_status/enable';
    const ORDER_STATUS_FROM = 'admin_notifications/general/order_status/from';
    const ORDER_STATUS_TO = 'admin_notifications/general/order_status/to';
    const ORDER_STATUS_EMAIL_TEMPLATE = 'admin_notifications/general/order_status/email_template';

    const STOCK_ENABLE = 'admin_notifications/general/stock/enable';
    const STOCK_LIMIT = 'admin_notifications/general/stock/limit';
    const STOCK_EMAIL_TEMPLATE = 'admin_notifications/general/stock/email_template';

    const REVIEW_ENABLE = 'admin_notifications/general/review/enable';
    const REVIEW_EMAIL_TEMPLATE = 'admin_notifications/general/review/email_template';

    const REGISTRATION_ENABLE = 'admin_notifications/general/registration/enable';
    const REGISTRATION_EMAIL_TEMPLATE = 'admin_notifications/general/registration/email_template';

    const NEWSLETTER_ENABLE = 'admin_notifications/general/newsletter/enable';
    const NEWSLETTER_EMAIL_TEMPLATE = 'admin_notifications/general/newsletter/email_template';

    const UNSUBSCRIPTION_ENABLE = 'admin_notifications/general/unsubscription/enable';
    const UNSUBSCRIPTION_EMAIL_TEMPLATE = 'admin_notifications/general/unsubscription/email_template';

    const WISHLIST_ENABLE = 'admin_notifications/general/wishlist/enable';
    const WISHLIST_EMAIL_TEMPLATE = 'admin_notifications/general/wishlist/email_template';

    private $timezone;
    private $storeManager;
    private $inlineTranslation;
    private $transportBuilder;

    public function __construct(
        Context $context,
        TimezoneInterface $timezone,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder
    ) {
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        parent::__construct($context);
    }

    public function getNewOrderConfig($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        try {
            if (!$this->scopeConfig->getValue(self::ENABLE, $scope) || !$this->scopeConfig->getValue(self::ORDER_ENABLE, $scope)) :
                return false;
            endif;
            $config = [];
            $config['email_template'] = $this->scopeConfig->getValue(self::ORDER_EMAIL_TEMPLATE, $scope);
            $config['receiver'] = $this->scopeConfig->getValue(self::RECEIVER, $scope);

            return $config;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getOrderStatusConfig($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        try {
            if (!$this->scopeConfig->getValue(self::ENABLE, $scope) || !$this->scopeConfig->getValue(self::ORDER_STATUS_ENABLE, $scope)) :
                return false;
            endif;
            $config = [];
            $config['to'] = $this->scopeConfig->getValue(self::ORDER_STATUS_TO, $scope);
            $config['email_template'] = $this->scopeConfig->getValue(self::ORDER_STATUS_EMAIL_TEMPLATE, $scope);
            $config['receiver'] = $this->scopeConfig->getValue(self::RECEIVER, $scope);

            return $config;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getStockConfig($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        try {
            if (!$this->scopeConfig->getValue(self::ENABLE, $scope) || !$this->scopeConfig->getValue(self::STOCK_ENABLE, $scope)) :
                return false;
            endif;
            $config = [];
            $config['limit'] = $this->scopeConfig->getValue(self::STOCK_LIMIT, $scope);
            $config['email_template'] = $this->scopeConfig->getValue(self::STOCK_EMAIL_TEMPLATE, $scope);
            $config['receiver'] = $this->scopeConfig->getValue(self::RECEIVER, $scope);

            return $config;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getReviewConfig($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        try {
            if (!$this->scopeConfig->getValue(self::ENABLE, $scope) || !$this->scopeConfig->getValue(self::REVIEW_ENABLE, $scope)) :
                return false;
            endif;
            $config = [];
            $config['email_template'] = $this->scopeConfig->getValue(self::REVIEW_EMAIL_TEMPLATE, $scope);
            $config['receiver'] = $this->scopeConfig->getValue(self::RECEIVER, $scope);

            return $config;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getRegistrationConfig($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        try {
            if (!$this->scopeConfig->getValue(self::ENABLE, $scope) || !$this->scopeConfig->getValue(self::REGISTRATION_ENABLE, $scope)) :
                return false;
            endif;
            $config = [];
            $config['email_template'] = $this->scopeConfig->getValue(self::REGISTRATION_EMAIL_TEMPLATE, $scope);
            $config['receiver'] = $this->scopeConfig->getValue(self::RECEIVER, $scope);

            return $config;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getNewsletterConfig($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        try {
            if (!$this->scopeConfig->getValue(self::ENABLE, $scope) || !$this->scopeConfig->getValue(self::NEWSLETTER_ENABLE, $scope)) :
                return false;
            endif;
            $config = [];
            $config['email_template'] = $this->scopeConfig->getValue(self::NEWSLETTER_EMAIL_TEMPLATE, $scope);
            $config['receiver'] = $this->scopeConfig->getValue(self::RECEIVER, $scope);

            return $config;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getUnsubscriptionConfig($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        try {
            if (!$this->scopeConfig->getValue(self::ENABLE, $scope) || !$this->scopeConfig->getValue(self::UNSUBSCRIPTION_ENABLE, $scope)) :
                return false;
            endif;
            $config = [];
            $config['email_template'] = $this->scopeConfig->getValue(self::UNSUBSCRIPTION_EMAIL_TEMPLATE, $scope);
            $config['receiver'] = $this->scopeConfig->getValue(self::RECEIVER, $scope);

            return $config;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getWishlistConfig($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        try {
            if (!$this->scopeConfig->getValue(self::ENABLE, $scope) || !$this->scopeConfig->getValue(self::WISHLIST_ENABLE, $scope)) :
                return false;
            endif;
            $config = [];
            $config['email_template'] = $this->scopeConfig->getValue(self::WISHLIST_EMAIL_TEMPLATE, $scope);
            $config['receiver'] = $this->scopeConfig->getValue(self::RECEIVER, $scope);

            return $config;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getCurrentTime()
    {
        try {
            return $this->timezone->date()->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function sendCustomMailSendMethod($config)
    {
        try {
            $config['store'] = $this->getStoreName();
            $this->inlineTranslation->suspend();
            $this->generateTemplate($config);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getStoreName()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    public function generateTemplate($config)
    {
        try {
            $this->transportBuilder->setTemplateIdentifier($config['email_template'])
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($config)
                ->setFrom($this->scopeConfig->getValue(self::SENDER, ScopeConfigInterface::SCOPE_TYPE_DEFAULT))
                ->addTo($config['receiver'], 'Admin');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $this;
    }
}
