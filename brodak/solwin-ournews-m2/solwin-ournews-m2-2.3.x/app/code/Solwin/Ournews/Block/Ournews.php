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

use Solwin\Ournews\Model\NewsFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Template\FilterProvider;

class Ournews extends Template
{
    /**
     * @var \Solwin\Ournews\Model\Resource\NewsPosts\Collection
     */
    protected $_collection;
    /**
     * @var \Solwin\Ournews\Model\NewsFactory
     */
    protected $_modelNewsFactory;

    /**
     * @param  \Magento\Framework\View\Element\Template\Context $context
     * @param  NewsFactory $modelNewsFactory
     * @param  \Solwin\Ournews\Model\ResourceModel\News\Collection $collection
     * @param  array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        NewsFactory $modelNewsFactory,
        \Solwin\Ournews\Model\ResourceModel\News\Collection $collection,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->_modelNewsFactory = $modelNewsFactory;
        $this->_collection = $collection;
        $this->_filterProvider = $filterProvider;
        parent::__construct($context, $data);
        $collection = $this->_modelNewsFactory->create()->getCollection();
        $this->setCollection($collection);
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $todaydate = date('Y-m-d H:i:s');
        if ($this->getCollection()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock(
                            'Magento\Theme\Block\Html\Pager',
                    'ournews.grid.record.pager'
                    )->setCollection(
                    $this->getCollection()
                            ->addStoreFilter(
                                $this->_storeManager->getStore()->getId()
                            )
                            ->addFieldToFilter('is_active', 1)
                            ->addfieldtofilter('start_publish_date', [
                                ['lteq' => date('Y-m-d',
                                        strtotime($todaydate)),
                                    'date' => true,
                                ]])
                            ->addfieldtofilter('end_publish_date', [
                                ['gteq' => date('Y-m-d',
                                        strtotime($todaydate)),
                                    'date' => true,
                                ]])
                    );
            $this->setChild('pager', $pager);
            $this->pageConfig->getTitle()->set(__('Our News'));
        }
        $currId = $this->getRequest()->getParam('id');

        if ($currId != '') {

            $newsCollection = self::getSingleNewsCollection($currId);
            $metaKeyword = $newsCollection->getMetaKeyword();
            $metaDescription = $newsCollection->getMetaDescription();

            $this->pageConfig->getTitle()->set($newsCollection->getTitle());
            $this->pageConfig->setKeywords($metaKeyword);
            $this->pageConfig->setDescription($metaDescription);
        }
        return $this;
    }

    /**
     * Get toolbar
     */
    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

    /**
     * get single news collection
     */
    public function getSingleNewsCollection($newsId) {
        $newsModel = $this->_modelNewsFactory->create();
        $newsCollection = $newsModel->load($newsId);
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

    /**
     * Get filtered content
     */
    public function filterContent($data) {
        return $this->_filterProvider->getBlockFilter()->filter($data);
    }
}