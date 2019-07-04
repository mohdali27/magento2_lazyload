<?php
namespace Potato\ImageOptimization\Logger;

use Monolog\Logger as PotatoLogger;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = PotatoLogger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/po_image_optimization.log';
}