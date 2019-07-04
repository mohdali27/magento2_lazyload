<?php
namespace Potato\Compressor\Helper;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class Data
{
    const COMPRESSOR_CACHE_TAG = 'POTATO_COMPRESSOR_CACHE';

    const MAGENTO_JS_FOLDER = 'js';
    const MAIN_FOLDER = '_po_compressor';
    const MERGED_IMAGE_FOLDER = 'po_cmp_image_merge';

    const FLAG_IGNORE_ALL = 'data-po-cmp-ignore';
    const FLAG_IGNORE_MOVE = 'data-po-cmp-ignore-move';
    const FLAG_IGNORE_MERGE = 'data-po-cmp-ignore-merge';
    const FLAG_IGNORE_MINIFY = 'data-po-cmp-ignore-minify';

    const INLINE_MAX_LENGTH = 1000;

    const IMAGE_PLACEHOLDER = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";

    const LIB_JS_BUILD_SCRIPT = 'mage/requirejs/static.js';
    const REQUIREJS_STORAGE_DIR = 'requirejs';

    /** @var Filesystem  */
    protected $filesystem;
    
    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var AssetRepository */
    protected $assetRepo;

    /**
     * @param Filesystem            $filesystem
     * @param StoreManagerInterface $storeManager
     * @param AssetRepository            $assetRepo
     */
    public function __construct(
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepo
    ) {
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->assetRepo = $assetRepo;
    }
    
    /**
     * @return string
     */
    public function getRootCachePath()
    {
        return rtrim($this->getAbsolutePath(DirectoryList::STATIC_VIEW), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . self::MAIN_FOLDER
        ;
    }

    /**
     * @param bool $isSecure
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRootCacheUrl($isSecure = false)
    {
        return rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_STATIC, $isSecure), '/')
            . '/'
            . self::MAIN_FOLDER
        ;
    }

    /**
     * @return string
     */
    public function getImageMergeCachePath()// this must ignore CDN urls
    {
        return rtrim($this->getAbsolutePath(DirectoryList::PUB), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . DirectoryList::STATIC_VIEW
            . DIRECTORY_SEPARATOR . self::MAIN_FOLDER
            . DIRECTORY_SEPARATOR . self::MERGED_IMAGE_FOLDER
        ;
    }

    /**
     * @param bool $isSecure
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageMergeCacheUrl($isSecure = false)// this must ignore CDN urls
    {
        return 	rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB, $isSecure), '/')
            . '/' . DirectoryList::PUB
            . '/' . DirectoryList::STATIC_VIEW
            . '/' . self::MAIN_FOLDER
            . '/' . self::MERGED_IMAGE_FOLDER
        ;
    }


    /**
     * @param string $requireJsUrl
     *
     * @return string
     */
    public function getRequireJsBuildScriptUrl($requireJsUrl)
    {
        $url = $this->assetRepo->getUrl(self::LIB_JS_BUILD_SCRIPT);
        if (FALSE === strpos($url, '/_view/')) {
            return $url;
        }
        $pos = strpos($url, '/_view/');
        $path = substr($requireJsUrl, $pos + 1);
        $parts = explode('/', $path);
        $theme = $parts[0] . '/' .$parts[1];
        return str_replace('/_view/', '/' . $theme . '/', $url);
    }


    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isOctal($value)
    {
        return decoct(octdec($value)) == $value;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getAbsolutePath($type)
    {
        return $this->filesystem->getDirectoryRead($type)->getAbsolutePath();
    }
}