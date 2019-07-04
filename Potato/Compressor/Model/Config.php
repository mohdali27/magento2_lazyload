<?php
namespace Potato\Compressor\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Model\Source\LazyLoad as LazyLoadSource;
use Potato\Compressor\Model\Source\ImageMerge as ImageMergeSource;

/**
 * Class Config
 */
class Config
{
    const GENERAL_ENABLED = 'po_compressor/general/is_enabled';
    const GENERAL_FOLDER_PERMISSION = 'po_compressor/advanced/folder_permission';
    const GENERAL_FILE_PERMISSION   = 'po_compressor/advanced/file_permission';

    const JS_ENABLED = 'po_compressor/js_settings/is_enabled';
    const JS_MERGE = 'po_compressor/js_settings/merge';
    const JS_MERGE_INLINE = 'po_compressor/js_settings/merge_inline';
    const JS_COMPRESSION = 'po_compressor/js_settings/compression';
    const JS_INLINE = 'po_compressor/js_settings/inline';
    const JS_DEFER = 'po_compressor/js_settings/defer';

    const CSS_ENABLED = 'po_compressor/css_settings/is_enabled';
    const CSS_MERGE = 'po_compressor/css_settings/merge';
    const CSS_MERGE_INLINE = 'po_compressor/css_settings/merge_inline';
    const CSS_COMPRESSION = 'po_compressor/css_settings/compression';
    const CSS_INLINE = 'po_compressor/css_settings/inline';
    const CSS_DEFER = 'po_compressor/css_settings/defer';

    const HTML_COMPRESSION = 'po_compressor/html/compression';

    const IMAGE_ENABLED = 'po_compressor/image/is_enabled';
    const IMAGE_LAZY_LOAD = 'po_compressor/image/lazy_load';
    const IMAGE_IDENTICAL_CONTENT = 'po_compressor/image/identical_content';
    const IMAGE_MERGE = 'po_compressor/image/inline_into_css';

    const ADVANCED_EXCLUDE = 'po_compressor/advanced/exclude';
    const ADVANCED_IMAGE_INLINE_INTO_CSS_MAX_FILE_SIZE = 'po_compressor/advanced/inline_into_css_file_size_limit';

    const CACHE_MANAGEMENT_MAX_SIZE = 'po_compressor/cache_management/max_size';

    const DEFAULT_PERMISSION = 0777;

    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DataHelper $dataHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DataHelper $dataHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function getFilePermission($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        $permission = $this->scopeConfig->getValue(
            self::GENERAL_FILE_PERMISSION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if (!$this->dataHelper->isOctal($permission)) {
            return self::DEFAULT_PERMISSION;
        }
        return intval($permission, 8);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function getFolderPermission($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        $permission = $this->scopeConfig->getValue(
            self::GENERAL_FOLDER_PERMISSION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if (!$this->dataHelper->isOctal($permission)) {
            return self::DEFAULT_PERMISSION;
        }
        return intval($permission, 8);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isJsEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::JS_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isJsMergeEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::JS_MERGE,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isJsEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isJsMergeInlineEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::JS_MERGE_INLINE,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isJsMergeEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isJsCompressionEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::JS_COMPRESSION,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isJsEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isJsInlineEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::JS_INLINE,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isJsEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isJsDeferEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::JS_DEFER,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isJsEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isCssEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::CSS_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isCssMergeEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::CSS_MERGE,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isCssEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isCssMergeInlineEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::CSS_MERGE_INLINE,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isCssMergeEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isCssCompressionEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::CSS_COMPRESSION,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isCssEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isCssInlineEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::CSS_INLINE,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isCssEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isCssDeferEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::CSS_DEFER,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isCssEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isHtmlCompressionEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::HTML_COMPRESSION,
            ScopeInterface::SCOPE_STORE,
            $store
        ) && $this->isEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isImageEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::IMAGE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        ) && $this->isEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isImageLazyLoadEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::IMAGE_LAZY_LOAD,
            ScopeInterface::SCOPE_STORE,
            $store
        ) && $this->isImageEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isImageLazyLoadOnLoadMode($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        if (!$this->isImageEnabled($store)) {
            return false;
        }
        $val = (int)$this->scopeConfig->getValue(
            self::IMAGE_LAZY_LOAD,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return $val === LazyLoadSource::LOAD_ALL_VALUE;
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isImageLazyLoadOnVisibleMode($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        if (!$this->isImageEnabled($store)) {
            return false;
        }
        $val = (int)$this->scopeConfig->getValue(
            self::IMAGE_LAZY_LOAD,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return $val === LazyLoadSource::LOAD_VISIBLE_VALUE;
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isImageFixIdenticalContent($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::IMAGE_IDENTICAL_CONTENT,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isImageEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return bool
     */
    public function isImageInlineIntoCSSEnabled($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
                self::IMAGE_MERGE,
                ScopeInterface::SCOPE_STORE,
                $store
            ) && $this->isImageEnabled($store);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return int|null
     */
    public function getImageMergeCSSMaxFileSizeInBytes($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        $val = (int)$this->scopeConfig->getValue(
            self::ADVANCED_IMAGE_INLINE_INTO_CSS_MAX_FILE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($val > 0) {
            return $val * 1024;
        }
        return null;
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return int|null
     */
    public function getCacheMaxSize($store = null)
    {
        $value = trim($this->scopeConfig->getValue(
            self::CACHE_MANAGEMENT_MAX_SIZE,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
        if (strlen($value) === 0) {
            return null;
        }
        return intval($value);
    }

    /**
     * @param null|string|bool|int|Store $store
     *
     * @return array
     */
    public function getExcludeAnchors($store = null)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        $rawText = trim($this->scopeConfig->getValue(
            self::ADVANCED_EXCLUDE,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
        if (strlen($rawText) === 0) {
            return [];
        }
        return explode(
            "\n",
            str_replace(["\r\n","\n\r","\r"],"\n", $rawText)
        );
    }
}
