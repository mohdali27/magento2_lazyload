<?php
namespace Potato\Compressor\Block\Adminhtml\System\Config\Cache;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Potato\Compressor\Model\Config;
use Potato\Compressor\Model\CacheManager;
use Potato\Compressor\Helper\Data as DataHelper;

class Status extends Field
{
    const CACHE_KEY_FOR_RESULT = 'POTATO_COMPRESSOR_SYSTEM_CONFIG_BLOCK_STATUS_RESULT';

    /** @var Config */
    protected $config;

    /** @var CacheManager */
    protected $cacheManager;

    /**
     * @param Context $context
     * @param Config $config
     * @param CacheManager $cacheManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        CacheManager $cacheManager,
        array $data = []
    ) {
        $this->config = $config;
        $this->cacheManager = $cacheManager;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->renderAjaxContainer();
        if ($cacheValue = $this->_cache->load(self::CACHE_KEY_FOR_RESULT)) {
            $html = $cacheValue;
        }
        if (strlen($html) === 0) {
            return '';
        }
        return '<tr>'
            . '<td class="label">' . __("Cache Status") . '</td>'
            . '<td class="value">' . $html . '</td></tr>';
    }

    /**
     * @return string
     */
    public function renderHtml()
    {
        $total = $this->config->getCacheMaxSize();
        $current = 0;
        $noteLabel = __('Cache limit is not defined');
        if (null!== $total && $total > 0) {
            $current = $this->cacheManager->calculateCacheSize()->getCacheSize();
            $current = round($current, 2);
            $noteLabel = __("%1 Mb used from %2 Mb available", $current, $total);
        }
        $progressHtml = '<progress class="po_compressor_progress_bar"'
            . ' value="' . $current . '" max="' . $total . '"></progress>';
        $noteHtml = '<div class="po_compressor_progress_bar__note">'
            . $noteLabel . '</div>';
        $result = $progressHtml . $noteHtml;
        $this->_cache->save(
            $result, self::CACHE_KEY_FOR_RESULT,
            [DataHelper::COMPRESSOR_CACHE_TAG],
            600
        );
        return $result;
    }

    /**
     * @return string
     */
    public function renderAjaxContainer()
    {
        $url = \Zend_Json::encode($this->getUrl('po_compressor/cache/status'));
        return '<div id="potato_compressor_cache_status_container"></div>'
            . '<script type="text/javascript">'
            . 'require(["Potato_Compressor/js/cache-status-loader"],function(loader){loader.run(' . $url . ');});'
            . '</script>';
    }
}