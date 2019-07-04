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

namespace Solwin\Ournews\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Element\Template;

class Rightsidebar extends Template
{
    protected $_template = 'Solwin_Ournews::ournews_sidebar.phtml';

    /**
     * @var \Solwin\Ournews\Model\Resource\NewsPosts\Collection
     */
    protected $_collection;

    /**
     * @param  \Solwin\Ournews\Model\ResourceModel\News\Collection $collection
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
    }

    public function getNewsCollection($newslimit) {
        $todaydate = date('Y-m-d H:i:s');
        $newsCollection = $this->_collection
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addFieldToFilter('is_active', 1)
                ->addfieldtofilter('start_publish_date', [
                    ['lteq' => date('Y-m-d', strtotime($todaydate)),
                        'date' => true,
                    ]])
                ->addfieldtofilter('end_publish_date', [
            ['gteq' => date('Y-m-d', strtotime($todaydate)),
                'date' => true,
            ]]);
        $newsCollection->getSelect()->limit($newslimit);
        return $newsCollection;
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
     * get media path
     */
    public function getMediaPath() {
        return $this->_filesystem->getDirectoryRead(
                        DirectoryList::MEDIA
                )->getAbsolutePath('');
    }

}