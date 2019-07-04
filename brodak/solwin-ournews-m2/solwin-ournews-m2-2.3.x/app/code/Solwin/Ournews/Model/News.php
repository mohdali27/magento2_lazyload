<?php
/**
 * Solwin Infotech
 * Solwin Ournews Extension
 *
 * @category   Solwin
 * @package    Solwin_Ournews
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
?>
<?php

namespace Solwin\Ournews\Model;

/**
 * @method News setTitle($title)
 * @method News setUrlKey($urlKey)
 * @method News setUrl($url)
 * @method News setImage($image)
 * @method News setShortdesc($shortdesc)
 * @method News setDescription($description)
 * @method News setStartPublishDate($startPublishDate)
 * @method News setEndPublishDate($endPublishDate)
 * @method News setIsActive($isActive)
 * @method News setMetaKeyword($metaKeyword)
 * @method News setMetaDescription($metaDescription)
 * @method mixed getTitle()
 * @method mixed getUrlKey()
 * @method mixed getUrl()
 * @method mixed getImage()
 * @method mixed getShortdesc()
 * @method mixed getDescription()
 * @method mixed getStartPublishDate()
 * @method mixed getEndPublishDate()
 * @method mixed getIsActive()
 * @method mixed getMetaKeyword()
 * @method mixed getMetaDescription()
 * @method News setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method News setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 */
class News extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'solwin_ournews_news';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = 'solwin_ournews_news';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'solwin_ournews_news';

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;
    
    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_newsHelper;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Solwin\Ournews\Model\ResourceModel\News $resource = null,
        \Solwin\Ournews\Model\ResourceModel\News\Collection $rCol = null,
        \Magento\Framework\UrlInterface $url,
        \Solwin\Ournews\Helper\Data $newsHelper,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->_newsHelper = $newsHelper;
        parent::__construct(
                $context,
                $registry,
                $resource,
                $rCol,
                $data
                );
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Solwin\Ournews\Model\ResourceModel\News');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
    
    public function getViewUrl()
    {
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $route = $this->_newsHelper->getConfig('newssection/newsgroup/route');
        $urlPrefixConfig = $this->_newsHelper
                ->getConfig('newssection/newsgroup/url_prefix');
        $urlPrefix = '';
        if ($urlPrefixConfig) {
            $urlPrefix = $urlPrefixConfig.'/';
        }
        $urlSuffix = $this->_newsHelper
                ->getConfig('newssection/newsgroup/url_suffix');
        return $url.$urlPrefix.$this->getUrlKey().$urlSuffix;
    }
}