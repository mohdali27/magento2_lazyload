<?php
namespace Potato\ImageOptimization\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Potato\ImageOptimization\Model\Source\System\OptimizationMethod;
use Magento\Framework\App\ObjectManager;

/**
 * Class Config
 */
class Config
{
    const GENERAL_ENABLED           = 'potato_image_optimization/general/is_enabled';
    const GENERAL_IMAGE_BACKUP      = 'potato_image_optimization/general/image_backup';

    const PNG_OPTIMIZATION_METHOD   = 'potato_image_optimization/png/optimization_method';
    const JPEG_OPTIMIZATION_METHOD   = 'potato_image_optimization/jpg/optimization_method';
    const GIF_OPTIMIZATION_METHOD   = 'potato_image_optimization/gif/optimization_method';

    const JPEG_COMPRESSION_LEVEL    = 'potato_image_optimization/jpg/compression_level';

    const INCLUDE_DIR_FOR_OPTIMIZATION    = 'potato_image_optimization/advanced/include_dirs';
    const EXCLUDE_DIR_FROM_OPTIMIZATION    = 'potato_image_optimization/advanced/exclude_dirs';

    const CUSTOM_PATH_TO_OPTIMIZATION_TOOLS    = 'potato_image_optimization/advanced/custom_tools_path';

    const DEFAULT_FOLDER_PERMISSION = 0775;
    const DEFAULT_FILE_PERMISSION = 0664;

    const SCAN_RUNNING_CACHE_KEY = 'po_imageoptimization_SCAN_RUNNING';
    const OPTIMIZATION_RUNNING_CACHE_KEY = 'po_imageoptimization_OPTIMIZTION_RUNNING';

    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var mixed|null  */
    protected $serializer = null;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        if (@class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $this->serializer = ObjectManager::getInstance()
                ->get('\Magento\Framework\Serialize\Serializer\Json');
        }
    }

    /**
     * @return bool
     */
    public function canPngUseService()
    {
        $result = $this->scopeConfig->getValue(self::PNG_OPTIMIZATION_METHOD);
        return $result === OptimizationMethod::USE_SERVICE;
    }

    /**
     * @return bool
     */
    public function canJpgUseService()
    {
        $result = $this->scopeConfig->getValue(self::JPEG_OPTIMIZATION_METHOD);
        return $result === OptimizationMethod::USE_SERVICE;
    }

    /**
     * @return bool
     */
    public function canGifUseService()
    {
        $result = $this->scopeConfig->getValue(self::GIF_OPTIMIZATION_METHOD);
        return $result === OptimizationMethod::USE_SERVICE;
    }

    /**
     * @return bool
     */
    public function isAllowImageBackup()
    {
        return (bool)$this->scopeConfig->getValue(self::GENERAL_IMAGE_BACKUP);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::GENERAL_ENABLED);
    }

    /**
     * @return string
     */
    public function getCompressionLevel()
    {
        return $this->scopeConfig->getValue(self::JPEG_COMPRESSION_LEVEL);
    }

    /**
     * @return int
     */
    public function getFolderPermission()
    {
        return self::DEFAULT_FOLDER_PERMISSION;
    }

    /**
     * @return int
     */
    public function getFilePermission()
    {
        return self::DEFAULT_FILE_PERMISSION;
    }

    /**
     * @return array
     */
    public function getIncludeDirs()
    {
        $dirs = $this->scopeConfig->getValue(self::INCLUDE_DIR_FOR_OPTIMIZATION);
        if (!$dirs) {
            return [];
        }
        if ($this->serializer) {
            $dirs = $this->serializer->unserialize($dirs);
        } else {
            $dirs = unserialize($dirs);
        }
        $result = [];
        foreach ($dirs as $dir) {
            $result[] = $dir['folder'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getExcludeDirs()
    {
        $dirs = $this->scopeConfig->getValue(self::EXCLUDE_DIR_FROM_OPTIMIZATION);
        if (!$dirs) {
            return [];
        }
        if ($this->serializer) {
            $dirs = $this->serializer->unserialize($dirs);
        } else {
            $dirs = unserialize($dirs);
        }
        $result = [];
        foreach ($dirs as $dir) {
            $result[] = $dir['folder'];
        }
        return $result;
    }

    /**
     * @return string|null
     */
    public function getCustomToolsPath()
    {
        return $this->scopeConfig->getValue(self::CUSTOM_PATH_TO_OPTIMIZATION_TOOLS);
    }
}
