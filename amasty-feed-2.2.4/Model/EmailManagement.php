<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Mail\Template\TransportBuilder;
use Amasty\Feed\Model\Config;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\Area;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\MailException;
use Amasty\Feed\Model\Feed;
use Magento\Framework\Exception\NoSuchEntityException;

class EmailManagement extends AbstractModel
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        TransportBuilder $transportBuilder,
        Config $config,
        StoreManagerInterface $storeManager,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->config = $config;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param Feed $feed
     * @param string $emailTemplate
     * @param null|string $errorMessage
     * @throws NoSuchEntityException
     */
    private function prepareSendEmail($feed, $emailTemplate, $errorMessage)
    {
        $emailSenderContact = $this->config->getEmailSenderContact();
        $emails = $this->config->getEmails();
        $storeId = $this->storeManager->getStore()->getId();

        /** @var Feed $feed */
        $templateVars = [
            'feed_id' => $feed->getEntityId(),
            'feed_name' => $feed->getName(),
            'date_time' => $feed->getGeneratedAt(),
            'generation_error' => $errorMessage
        ];

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $emailTemplate
        )->setTemplateOptions(
            ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
        )->setFrom(
            $emailSenderContact
        )->setTemplateVars(
            $templateVars
        )->addTo(
            $emails
        )->getTransport();

        $this->setTransport($transport);
    }

    /**
     * @param Feed $feed
     * @param string $emailTemplate
     * @param null $errorMessage
     * @return $this
     * @throws NoSuchEntityException
     */
    public function sendEmail($feed, $emailTemplate, $errorMessage = null)
    {
        $this->prepareSendEmail($feed, $emailTemplate, $errorMessage);
        try {
            $this->getTransport()->sendMessage();
        } catch (MailException $e) {
            $this->_logger->critical($e);
        }

        return $this;
    }
}
