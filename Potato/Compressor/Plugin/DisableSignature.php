<?php
namespace Potato\Compressor\Plugin;

use Magento\Theme\Model\Url\Plugin\Signature;
use Magento\Framework\View\Url\ConfigInterface;
use Magento\Framework\App\View\Deployment\Version as DeploymentVersion;
use Potato\Compressor\Model\Config;

class DisableSignature extends Signature
{
    /** @var \Magento\Framework\View\Url\ConfigInterface */
    private $config;

    /** @var \Magento\Framework\App\View\Deployment\Version */
    private $deploymentVersion;

    /** @var  \Potato\Compressor\Model\Config */
    protected $compressorConfig;

    /**
     * @param ConfigInterface $config
     * @param DeploymentVersion $deploymentVersion
     * @param Config $compressorConfig
     */
    public function __construct(
        ConfigInterface $config,
        DeploymentVersion $deploymentVersion,
        Config $compressorConfig
    ) {
        $this->config = $config;
        $this->deploymentVersion = $deploymentVersion;
        $this->compressorConfig = $compressorConfig;
        parent::__construct($config, $deploymentVersion);
    }

    /**
     * Whether signing of URLs is enabled or not
     *
     * @return bool
     */
    protected function isUrlSignatureEnabled()
    {
        if ($this->compressorConfig->isEnabled()) {
            return false;
        }
        return (bool)$this->config->getValue(self::XML_PATH_STATIC_FILE_SIGNATURE);
    }
}
