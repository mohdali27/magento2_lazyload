<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\Locator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Backend\Model\Session;
/**
 * Class RegistryLocator
 */
class Locator implements \Magestore\InventorySuccess\Model\Locator\LocatorInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry,
        Session $session
    ) {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentTransferstockId()
    {
        $id = $this->session->getData('current_transferstock_id');
        if($id){
            return $id;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentTransferstockId($id)
    {
        $this->session->setData('current_transferstock_id',$id);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshSession()
    {
        $this->session->setData('current_transferstock_id',null);
    }

    /**
     * {@inheritdoc}
     */
    public function getSesionByKey($key)
    {
        $id = $this->session->getData($key);
        if($id){
            return $id;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setSesionByKey($key, $data)
    {
        $this->session->setData($key, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshSessionByKey($key)
    {
        $this->session->setData($key, null);
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionByKey($key)
    {
        $id = $this->session->getData($key);
        if($id){
            return $id;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setSessionByKey($key, $data)
    {
        $this->session->setData($key, $data);
    }

}
