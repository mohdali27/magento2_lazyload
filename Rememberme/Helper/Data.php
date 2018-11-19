<?php

namespace Webapp\Rememberme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;


class Data extends AbstractHelper
{

   /**
    * Name of Cookie that holds private content version
    */
   CONST COOKIE_NAME = 'remember';

   /**
    * Cookie life time
    */
   CONST COOKIE_LIFE = 604800;

   /**
    * @var \Magento\Framework\Stdlib\CookieManagerInterface
    */
   protected $cookieManager;

   /**
    * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
    */
   protected $cookieMetadataFactory;

   /**
    * @var $scopeConfigInterface
    */
   private $scopeConfigInterface;

   /**
    * @var \Magento\Framework\Session\SessionManagerInterface
    */
   protected $sessionManager;


   public function __construct(
       ScopeConfigInterface $scopeConfigInterface,
       CookieManagerInterface $cookieManager,
       CookieMetadataFactory $cookieMetadataFactory,
       SessionManagerInterface $sessionManager
   ){
       $this->scopeConfigInterface = $scopeConfigInterface;
       $this->cookieManager = $cookieManager;
       $this->cookieMetadataFactory = $cookieMetadataFactory;
       $this->sessionManager = $sessionManager;
   }

   /**
    * Get data from cookie set in remote address
    *
    * @return value
    */
   public function get($name)
   {
       return $this->cookieManager->getCookie($name);
   }

   /**
    * Set data to cookie in remote address
    *
    * @param [string] $value    [value of cookie]
    * @param integer $duration [duration for cookie] 7 Days
    *
    * @return void
    */
   public function set($value, $duration = 604800)
   {
       $metadata = $this->cookieMetadataFactory
           ->createPublicCookieMetadata()
           ->setDuration($duration)
           ->setPath($this->sessionManager->getCookiePath())
           ->setDomain($this->sessionManager->getCookieDomain());

       $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $value, $metadata);

   }

   /**
    * delete cookie remote address
    *
    * @return void
    */
   public function delete($name)
   {
       $this->cookieManager->deleteCookie(
           $name,
           $this->cookieMetadataFactory
               ->createCookieMetadata()
               ->setPath($this->sessionManager->getCookiePath())
               ->setDomain($this->sessionManager->getCookieDomain())
       );
   }

   /**
    * @return \Dckap\Rememberme\Helper
    */
   public function getCookieloginName()
   {
       $name = json_decode($this->get(self::COOKIE_NAME));
       if($name)
       return $name->username ? $name->username : '';
   }

   /**
    * @return \Dckap\Rememberme\Helper
    */
   public function getCookieloginPwd()
   {
       $pwd = json_decode($this->get(self::COOKIE_NAME));
       if($pwd)
       return $pwd->password ? $pwd->password : '';
   }

   /**
    * @return \Dckap\Rememberme\Helper
    */
   public function getCookieloginChk()
   {
       $chk = json_decode($this->get(self::COOKIE_NAME));
       if($chk)
       return $chk->remchkbox ? 1 : '';
   }

   /**
    * @return var
    */
   public function getCookielifetime()
   {
       return self::COOKIE_LIFE;
   }
}
