<?php

namespace Potato\ImageOptimization\Cron;

use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\App;

class AppOptimization
{
    /** @var Config  */
    protected $config;

    /** @var App  */
    protected $app;

    /**
     * AppOptimization constructor.
     * @param Config $config
     * @param App $app
     */
    public function __construct(
        Config $config,
        App $app
    ) {
        $this->config = $config;
        $this->app = $app;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }
        $this->app->prepareAndSendServiceImages();
        return $this;
    }
}
