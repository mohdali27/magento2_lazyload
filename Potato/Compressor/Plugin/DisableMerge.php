<?php
namespace Potato\Compressor\Plugin;

use Magento\Framework\View\Asset\Config as AssetConfig;
use Potato\Compressor\Model\Config as CompressorConfig;

class DisableMerge
{
    /** @var CompressorConfig */
    protected $config;

    /**
     * @param CompressorConfig $config
     */
    public function __construct(
        CompressorConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * @param AssetConfig $subject
     * @param \Closure $proceed
     *
     * @return mixed
     */
    public function aroundIsMergeCssFiles(
        AssetConfig $subject,
        \Closure $proceed
    ) {
        return $this->disable($proceed);
    }

    /**
     * @param AssetConfig $subject
     * @param \Closure $proceed
     *
     * @return mixed
     */
    public function aroundIsMergeJsFiles(
        AssetConfig $subject,
        \Closure $proceed
    ) {
        return $this->disable($proceed);
    }

    /**
     * @param AssetConfig $subject
     * @param \Closure $proceed
     *
     * @return mixed
     */
    public function aroundIsMinifyHtml(
        AssetConfig $subject,
        \Closure $proceed
    ) {
        return $this->disable($proceed);
    }

    /**
     * @param AssetConfig $subject
     * @param \Closure $proceed
     *
     * @return mixed
     */
    public function aroundIsBundlingJsFiles(
        AssetConfig $subject,
        \Closure $proceed
    ) {
        return $this->disable($proceed);
    }

    /**
     * @param \Closure $proceed
     *
     * @return bool
     */
    protected function disable(\Closure $proceed)
    {
        if ($this->config->isEnabled()) {
            return false;
        }
        return $proceed();
    }
}
