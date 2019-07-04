<?php
namespace Potato\Compressor\Model;

use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\CacheInvalidate\Model\PurgeCache as VarnishPurgeCache;
use Magento\PageCache\Model\Cache\Type as BuiltInPageCache;

class PageCachePurger
{
    /** @var PageCacheConfig */
    protected $pageCacheConfig;

    /** @var VarnishPurgeCache */
    protected $varnishPurgeCache;

    /** @var BuiltInPageCache */
    protected $builtInPageCache;

    /**
     * @param PageCacheConfig $pageCacheConfig
     * @param VarnishPurgeCache $varnishPurgeCache
     * @param BuiltInPageCache $builtInPageCache
     */
    public function __construct(
        PageCacheConfig $pageCacheConfig,
        VarnishPurgeCache $varnishPurgeCache,
        BuiltInPageCache $builtInPageCache
    ) {
        $this->pageCacheConfig = $pageCacheConfig;
        $this->varnishPurgeCache = $varnishPurgeCache;
        $this->builtInPageCache = $builtInPageCache;
    }

    /**
     * @param string $tags
     *
     * @return $this
     */
    public function purgeByTags($tags)
    {
        if (!$this->pageCacheConfig->isEnabled()) {
            return $this;
        }
        $type = $this->pageCacheConfig->getType();
        switch ($type) {
            case PageCacheConfig::VARNISH:
                $this->_varnishPurgeByTagListString($tags);
                break;
            case PageCacheConfig::BUILT_IN:
                $this->_builtInPageCachePurgeByTagListString($tags);
                break;
        }
        return $this;
    }

    /**
     * @param string $tagListString
     *
     * @return $this
     */
    protected function _varnishPurgeByTagListString($tagListString)
    {
        $this->varnishPurgeCache->sendPurgeRequest($tagListString);
        return $this;
    }

    /**
     * @param string $tagListString
     *
     * @return $this
     */
    protected function _builtInPageCachePurgeByTagListString($tagListString)
    {
        $tagList = explode(',', $tagListString);
        $this->builtInPageCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tagList);
        return $this;
    }
}