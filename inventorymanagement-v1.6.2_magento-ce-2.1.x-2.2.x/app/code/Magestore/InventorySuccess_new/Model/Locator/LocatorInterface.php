<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\Locator;

/**
 * Interface LocatorInterface
 */
interface LocatorInterface
{
    /**
     * @return mixed
     */
    public function refreshSession();

    /**
     * @return mixed
     */
    public function getCurrentTransferstockId();

    /**
     * @param HistoryInterface
     * @return mixed
     */
    public function setCurrentTransferstockId($id);

    /**
     * @return mixed
     */
    public function refreshSessionByKey($key);

    /**
     * @param string
     * @return mixed
     */
    public function getSesionByKey($key);

    /**
     * @param string string
     * @return
     */
    public function setSesionByKey($key, $data);
}
