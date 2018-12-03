<?php
/**
 * Activo Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Activo Commercial License
 * that is available through the world-wide-web at this URL:
 * http://extensions.activo.com/license_professional
 *
 * @copyright   Copyright (c) 2017 Activo Extensions (http://extensions.activo.com)
 * @license     Commercial
 */
namespace Activo\BulkImages\Model\ResourceModel\Import;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Activo\BulkImages\Model\Import', 'Activo\BulkImages\Model\ResourceModel\Import');
    }

    public function getConnection()
    {
        return $this->_resource->getConnection();
    }

    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }
}
