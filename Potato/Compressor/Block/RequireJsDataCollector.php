<?php
namespace Potato\Compressor\Block;

use Magento\Framework\View\Element\Template;
use Potato\Compressor\Model\Config;
use Potato\Compressor\Model\RequireJsManager;

class RequireJsDataCollector extends Template
{
    /** @var Config  */
    protected $config;

    /** @var  RequireJsManager */
    protected $requireJsManager;

    /**
     * RequireJsDataCollector constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param RequireJsManager $requireJsManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        RequireJsManager $requireJsManager,
        array $data = []
    ) {
        $this->config = $config;
        $this->requireJsManager = $requireJsManager;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->isJsMergeEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->getUrl('po_compressor/js/collect');
    }

    /**
     * @return string
     */
    public function getScriptDataKey()
    {
        return RequireJsManager::SCRIPT_TAG_DATA_KEY;
    }

    /**
     * @return string
     */
    public function getPageCacheTags()
    {
        return RequireJsManager::TAG_VALUE_PLACEHOLDER;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRouteKey()
    {
        return $this->requireJsManager->getRouteKeyByLayout($this->getLayout());
    }

    /**
     * @return string[]
     */
    public function getIgnore()
    {
        $result = [];
        $list = $this->config->getExcludeAnchors();
        foreach ($list as $value) {
            $result[] = base64_encode($value);
        }
        return $result;
    }
}
