<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Rewrite\VisualMerchandiser\Model\Position;

class Cache extends \Magento\Framework\Model\AbstractModel
{
    const POSITION_CACHE_KEY = 'position_cache_key';
    const CACHE_PREFIX = 'MERCHANDISER_POSITION_CACHE_';

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $backendConfig;

    /**
     * @var array|null
     */
    protected $cachedData = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Config\Model\Config $backendConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\Config $backendConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->cache = $context->getCacheManager();
        $this->backendConfig = $backendConfig;
    }

    /**
     * @param string $key
     * @param array $positions
     * @param int|null $sortOrder
     * @return void
     */
    public function saveData($key, $positions, $sortOrder = null)
    {
        $lifeTime = $this->backendConfig->getConfigDataValue(
            \Magento\Backend\Model\Auth\Session::XML_PATH_SESSION_LIFETIME
        );

        if (!is_numeric($lifeTime)) {
            $lifeTime = null;
        }

        $data['positions'] = $positions;

        if ($sortOrder !== null) {
            $data['sort_order'] = $sortOrder;
        }

        $this->cache->save(\Zend_Json::encode($data), self::CACHE_PREFIX . $key, [], $lifeTime);
    }

    /**
     * @param string $cacheKey
     * @param string $param
     * @return null|mixed
     */
    private function getFromCache($cacheKey, $param)
    {
        if (!$cacheKey) {
            return false;
        }

        if ($this->cachedData == null) {
            $jsonStr = $this->cache->load(self::CACHE_PREFIX . $cacheKey);
            if (strlen($jsonStr)) {
                $this->cachedData = \Zend_Json::decode($jsonStr);
            }
        }

        return isset($this->cachedData[$param]) ? $this->cachedData[$param] : false;
    }

    /**
     * @param string $key
     * @return bool|array
     */
    public function getPositions($key)
    {
        $positions = $this->getFromCache($key, 'positions');

        if ($positions !== false) {
            if (!is_array($positions)) {
                return false;
            }

            $positionsFiltered = [];
            foreach ($positions as $key => $value) {
                if (is_numeric($key) && is_numeric($value)) {
                    $positionsFiltered[$key] = $value;
                }
            }

            return $positionsFiltered;
        }
        return false;
    }

    /**
     * @param string $key
     * @return bool|int
     */
    public function getSortOrder($key)
    {
        return $this->getFromCache($key, 'sort_order');
    }

    /**
     * @param string $key
     * @param array $data
     * @return void
     */
    public function prependPositions($key, $data)
    {
        $positions = $this->getPositions($key);
        $filteredData = [];
        foreach ($data as $item) {
            if (!array_key_exists($item, $positions)) {
                $filteredData[$item] = 0;
            }
        }
        $data = $this->reorderPositions($filteredData + $positions);
        $this->saveData($key, $data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function reorderPositions($data)
    {
        $positionIndex = 0;
        $finalData = [];
        foreach (array_keys($data) as $dataKey) {
            $finalData[$dataKey] = $positionIndex;
            $positionIndex++;
        }

        return $finalData;
    }
}
