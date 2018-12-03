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
namespace Activo\BulkImages\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{

    protected $loggerType = Logger::INFO;
    protected $fileName = '/var/log/activo/activo_bulkimages.log';
}
