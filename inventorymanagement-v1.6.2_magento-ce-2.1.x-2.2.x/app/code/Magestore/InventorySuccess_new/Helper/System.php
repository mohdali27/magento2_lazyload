<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Helper;

use Magestore\InventorySuccess\Api\Helper\SystemInterface;
use Magento\User\Api\Data\UserInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class System implements SystemInterface
{
    
    /**
     * @var DateTime
     */
    protected $dateTime;
    
    /**
     *
     * @var \Magento\Backend\Model\Auth\Session 
     */
    protected $authSession;
    
    
    public function __construct(
            DateTime $dateTime,
            \Magento\Backend\Model\Auth\Session $authSession
    )
    {
        $this->dateTime = $dateTime;
        $this->authSession = $authSession;
    }    
    
    /**
     * Get current timestamp
     * 
     * @return string
     */    
    public function getCurTime()
    {
        return $this->dateTime->gmtDate();
    }
    
    /**
     * Get current admin user
     * 
     * @return UserInterface
     */
    public function getCurUser()
    {
        return $this->authSession->getUser();
    }
    
    /**
     * Get current unix time stamp
     * 
     * @return int
     */
    public function getUnixTime()
    {
        return $this->dateTime->timestamp();
    }
    
    /**
     * 
     * @return boolean
     */
    public function isAdminArea()
    {
        if($this->authSession->isLoggedIn()) {
            return true;
        }
        return false;
    }

}
