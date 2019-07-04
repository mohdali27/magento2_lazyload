<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 07/09/2016
 * Time: 22:26
 */

namespace Magestore\InventorySuccess\Model\Email;


class EmailManagement
{

    /** @var array of name and email of the sender ['name'=>'sender_name', 'email'=>'steve@magetore.com']  */
    protected $_sender;

    /** @var  array of receiver emails: ['receiver1@magestore.com', 'receiver2@gmail.com'] */
    protected $_receivers;

    /** @var   */
    protected $_templateVars;

    /** @var   */
    protected $_emailTemplate;

    /**
     * @return array
     */
    public function getSender(){
        if(!$this->_sender){
            /** @var  \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
            $scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magento\Framework\App\Config\ScopeConfigInterface'
            );
            $sender = [
                'name' => $scopeConfig->getValue('trans_email/ident_general/name'),
                'email' => $scopeConfig->getValue('trans_email/ident_general/email'),
            ];
            $this->_sender = $sender;
        }
        return $this->_sender;
    }

    /**
     * @param $sender
     */
    public function setSender($sender){
        $this->_sender = $sender;
    }

    /**
     * @return array
     */
    public function getReceivers(){
        return $this->_receivers;
    }

    /**
     * @param $receiver
     */
    public function setReceivers($receivers){
        $this->_receivers = $receivers;
    }

    /**
     * @param $templateVars
     */
    public function setTemplateVars($templateVars){
        $this->_templateVars = $templateVars;
    }

    /**
     * @return mixed
     */
    public function getTemplateVars(){
        return $this->_templateVars;
    }

    /**
     * @return mixed
     */
    public function getEmailTemplate(){
        return $this->_emailTemplate;
    }

    /**
     * @param $emailTemplate
     */
    public function setEmailTemplate($emailTemplate){
        $this->_emailTemplate = $emailTemplate;
    }

    /**
     *
     */
    public function sendEmail(){
        /** @var \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder */
        $transportBuilder = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Mail\Template\TransportBuilder'
        );
        /** @var  \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
        $scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\App\Config\ScopeConfigInterface'
        );
        
        $notifierEmails = explode(',', $this->getReceivers());
        foreach ($notifierEmails as $email) {
            try {
                if($email){
                $transport = $transportBuilder
                    ->setTemplateIdentifier($this->getEmailTemplate())
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars($this->getTemplateVars())
                    ->setFrom($this->getSender())
                    ->addTo(trim($email))
                    ->getTransport();
                $transport->sendMessage();
                }
            } catch (\Exception $e) {
                return;
            }
        }
    }
}