<?php
namespace Potato\Compressor\Model;

use Magento\Framework\View\LayoutInterface;
use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Helper\File as FileHelper;
use Magento\Framework\View\Element\Template\Context;
use Potato\Compressor\Model\Optimisation\Processor\Minifier\Js as JsMinify;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp\Js as JsFinder;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Helper\HtmlParser;

class RequireJsManager
{
    const SCRIPT_TAG_DATA_KEY = 'data-po-cmp-requirejs-key';
    const CACHE_KEY_PREFIX = 'POTATO_COMPRESSOR_REQUIREJS_';
    const TAG_VALUE_PLACEHOLDER = '{{tags}}';

    /** @var  Config */
    protected $config;

    /** @var  CacheInterface */
    protected $cache;

    /** @var  FileHelper */
    protected $fileHelper;

    /** @var  Context */
    protected $context;

    /** @var  JsMinify */
    protected $jsMinify;

    /** @var  JsFinder */
    protected $jsFinder;

    public function __construct(
        Config $config,
        FileHelper $fileHelper,
        Context $context,
        JsMinify $jsMinify,
        JsFinder $jsFinder
    ) {
        $this->config = $config;
        $this->cache = $context->getCache();
        $this->fileHelper = $fileHelper;
        $this->context = $context;
        $this->jsMinify = $jsMinify;
        $this->jsFinder = $jsFinder;
    }

    /**
     * @param LayoutInterface $layout
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRouteKeyByLayout(LayoutInterface $layout)
    {
        $handleList = $layout->getUpdate()->getHandles();
        $handleList = array_slice($handleList, 0, 2);
        $result = join('---', $handleList);
        $result .= '||' . md5($this->context->getDesignPackage()->getDesignTheme()->getCode());
        $result .= '||' . $this->context->getStoreManager()->getStore()->getId();
        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRouteKeyByCurrentContext()
    {
        return $this->getRouteKeyByLayout($this->context->getLayout());
    }

    /**
     * @param string $routeKey
     *
     * @return bool
     */
    public function isDataExists($routeKey)
    {
        return (bool)$this->cache->getFrontend()->test(self::CACHE_KEY_PREFIX . $routeKey);
    }

    /**
     * @param array $list
     * @param string $routeKey
     *
     * @return bool
     */
    public function saveUrlList($list, $routeKey)
    {
        return $this->cache->save(
            \Zend_Json::encode(array_unique($list)),
            self::CACHE_KEY_PREFIX . $routeKey,
            [DataHelper::COMPRESSOR_CACHE_TAG],
            null
        );
    }

    /**
     * @param string $routeKey
     *
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadUrlList($routeKey)
    {
        if (null === $routeKey) {
            $routeKey = $this->getRouteKeyByCurrentContext();
        }
        $data = $this->cache->load(
            self::CACHE_KEY_PREFIX . $routeKey
        );
        if (!is_string($data)) {
            return null;
        }
        try {
            $result = \Zend_Json::decode($data);
        } catch (\Zend_Json_Exception $e) {
            return null;
        }
        return $result;
    }

    /**
     * @param string $routeKey
     *
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadFileList($routeKey)
    {
        $list = $this->loadUrlList($routeKey);
        if (null === $list) {
            return null;
        }
        $result = [];
        foreach ($list as $url) {
            if (!$this->fileHelper->isInternalUrl($url)) {
                continue;
            }
            $result[] = $this->fileHelper->getLocalPathFromUrl($url);
        }
        return $result;
    }

    /**
     * @param string $routeKey
     * @param string $fileExtension
     * @param null $callbackOnContent
     *
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getInlineConfig($routeKey, $fileExtension = '.js', $callbackOnContent = null)
    {
        $urlList = $this->loadUrlList($routeKey);
        if (null === $urlList) {
            return null;
        }
        $baseUrl = array_shift($urlList);
        $excludeAnchorList = $this->config->getExcludeAnchors();
        $config = [];
        foreach ($urlList as $url) {
            if (!$this->fileHelper->isInternalUrl($url)) {
                continue;
            }
            $currentFileExtension = substr($url, strlen($fileExtension) * -1);
            if ($currentFileExtension !== $fileExtension) {
                continue;
            }
            $baseUrlOffset = count(explode('/', $baseUrl)) - 1;
            $urlParts = explode('/', $url);            
            $key = join(array_slice($urlParts, $baseUrlOffset), '/');
            $content = $this->fileHelper->getFileContentByUrl($url);
            $isExit = false;
            foreach ($excludeAnchorList as $anchor) {
                if (FALSE !== strpos($url, $anchor)) {
                    $isExit = true;
                    continue;
                }
            }
            if ($isExit) {
                $config[$key] = $content;
                continue;
            }
            if (null !== $callbackOnContent && is_callable($callbackOnContent)) {
                try {
                    $content = call_user_func($callbackOnContent, $content);
                } catch (\Exception $e) {}
            }
            $config[$key] = $content;
        }
        if (array_key_exists(DataHelper::LIB_JS_BUILD_SCRIPT, $config)) {
            unset($config[DataHelper::LIB_JS_BUILD_SCRIPT]);
        }
        return $config;
    }

    /**
     * @param string $routeKey
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRequireJsContent($routeKey)
    {
        $jsCallback = null;
        $htmlCallback = null;
        if ($this->config->isJsCompressionEnabled()) {
            $jsCallback = [
                $this->jsMinify,
                'minifyContent'
            ];
        }
        if ($this->config->isHtmlCompressionEnabled()) {
            $htmlCallback = [
                '\Potato\Compressor\Lib\Minify\HTMLMax',
                'minify'
            ];
        }
        $config = [
            'jsbuild' => $this->getInlineConfig($routeKey, '.js', $jsCallback),
            'text' => $this->getInlineConfig($routeKey, '.html', $htmlCallback)
        ];

        $content = "require.config({config:" . \Zend_Json::encode($config) . "});";
        $content .= <<<EOL
require.config({
    bundles: {
        'mage/requirejs/static': [
            'jsbuild',
            'buildTools',
            'text',
            'statistician'
        ]
    },
    deps: ['jsbuild']
});
EOL;
        return $content;
    }

    /**
     * @param ResponseHttp $response
     *
     * @return $this
     * @throws \Exception
     */
    public function aroundControllerRenderResultCall(ResponseHttp $response)
    {
        $header = $response->getHeader('X-Magento-Tags');
        $pageCacheTagList = '';
        if (is_array($header) || $header instanceof \ArrayIterator) {
            $list = [];
            foreach ($header as $value) {
                $list[] = $value->getFieldValue();
            }
            $pageCacheTagList = join(',', $list);
        } else if ($header) {
            $pageCacheTagList = $header->getFieldValue();
        }
        $html = $response->getBody();
        $tagsList = $this->jsFinder->findInline($html);
        $replaceData = array();
        foreach ($tagsList as $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            if (!array_key_exists(self::SCRIPT_TAG_DATA_KEY, $attributes)) {
                continue;
            }
            preg_match('/^(<script[^>]*?>)(.*)(<\/script>)$/is', $tag->getContent(), $matches);
            if (count($matches) === 0) {
                continue;
            }
            $content = $matches[2];
            $content = str_replace(self::TAG_VALUE_PLACEHOLDER, $pageCacheTagList, $content);
            $replaceData[] = array(
                'start' => $tag->getStart(),
                'end' => $tag->getEnd(),
                'content' => $matches[1] . $content . $matches[3]
            );
        }
        foreach (array_reverse($replaceData) as $replaceElData) {
            $html = HtmlParser::replaceIntoHtml(
                $html, $replaceElData['content'], $replaceElData['start'], $replaceElData['end']
            );
        }
        $response->setBody($html);
        return $this;
    }
}
