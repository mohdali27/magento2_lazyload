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

namespace Solwin\Ournews\Block\Widget;

use Magento\Framework\App\Filesystem\DirectoryList;

class OurnewsWidget extends \Magento\Framework\View\Element\Template
implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var  \Solwin\Ournews\Model\ResourceModel\News\Collection 
     */
    protected $_collection;

    /**
     * @param  \Magento\Catalog\Block\Product\Context $context
     * @param   array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Solwin\Ournews\Model\ResourceModel\News\Collection $collection,
        array $data = []
    ) {
        $this->_collection = $collection;
        parent::__construct($context, $data);
        $this->setTemplate('widget/ournewswidget.phtml');
    }

    /**
     * get news collection
     */
    public function getNewsData() {
        $collection = $this->_collection
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addFieldToFilter('is_active', 1);

        return $collection;
    }
    
    /**
     * get base url with store code
     */
    public function getBaseUrlWithStoreCode() {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * get base url without store code
     */
    public function getBaseUrl() {
        return $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
    
    /**
     *  get media path
     */
    public function getMediaPath() {
        return $this->_filesystem->getDirectoryRead(
                        DirectoryList::MEDIA
                )->getAbsolutePath('');
    }
    
    /**
     *  get default image
     */
    public function getDefaultImage() {
        return $this->_assetRepo->getUrl('Solwin_Ournews::images/notfound.png');
    }
  
}