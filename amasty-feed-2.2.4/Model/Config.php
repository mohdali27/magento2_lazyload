<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

class Config
{
    /**#@+
     * Configuration paths
     */
    const FEED_SECTION = 'amasty_feed/';

    const GENERAL_GROUP = 'general/';

    const NOTIFICATION_GROUP = 'notifications/';

    const BATCH_SIZE_FIELD = 'batch_size';

    const PREVIEW_ITEMS = 'preview_items';

    const EVENTS_FIELD = 'events';

    const SENDER_FIELD = 'email_sender';

    const EMAILS_FIELD = 'emails';

    const SUCCESS_TEMPLATE_FIELD = 'success_template';

    const UNSUCCESS_TEMPLATE_FIELD = 'unsuccess_template';
    /**#@-*/

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $group
     * @param string $path
     *
     * @return mixed
     */
    private function getScopeValue($group, $path)
    {
        return $this->config->getValue(
            self::FEED_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $group
     * @param string $path
     *
     * @return bool
     */
    private function isSetFlag($group, $path)
    {
        return $this->config->isSetFlag(
            self::FEED_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getItemsForPreview()
    {
        return (int)$this->getScopeValue(self::GENERAL_GROUP, self::PREVIEW_ITEMS);
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return (int)$this->getScopeValue(self::GENERAL_GROUP, self::BATCH_SIZE_FIELD);
    }

    /**
     * @return string
     */
    public function getSelectedEvents()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::EVENTS_FIELD);
    }

    /**
     * @return string
     */
    public function getSuccessEmailTemplate()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::SUCCESS_TEMPLATE_FIELD);
    }

    /**
     * @return string
     */
    public function getUnsuccessEmailTemplate()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::UNSUCCESS_TEMPLATE_FIELD);
    }

    /**
     * @return string
     */
    public function getEmailSenderContact()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::SENDER_FIELD);
    }

    /**
     * @return string
     */
    public function getEmails()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::EMAILS_FIELD);
    }
}
