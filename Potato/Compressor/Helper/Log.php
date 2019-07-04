<?php
namespace Potato\Compressor\Helper;

use Potato\Compressor\Logger\Logger;

class Log
{
    /** @var Logger */
    protected $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }
    
    /**
     * @param string $message
     */
    public function processorLog($message)
    {
        $this->logger->error($message);
    }

    /**
     * @param string $message
     */
    public function log($message)
    {
        $this->logger->error($message);
    }
}
