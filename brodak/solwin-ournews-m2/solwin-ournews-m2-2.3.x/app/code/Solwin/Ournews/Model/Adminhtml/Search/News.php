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

namespace Solwin\Ournews\Model\Adminhtml\Search;

use Solwin\Ournews\Model\ResourceModel\News\CollectionFactory; 

class News extends \Magento\Framework\DataObject
{
    /**
     * News Collection factory
     * 
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Backend data helper
     * 
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData;

    /**
     * constructor
     * 
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $adminhtmlData
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_adminhtmlData     = $adminhtmlData;
        parent::__construct();
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $query = $this->getQuery();
        $collection = $this->_collectionFactory->create()
            ->addFieldToFilter('title', ['like' => '%'.$query.'%'])
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();

        foreach ($collection as $news) {
            $result[] = [
                'id' => 'solwin_ournews_news/1/' . $news->getId(),
                'type' => __('News'),
                'name' => $news->getTitle(),
                'description' => $news->getTitle(),
                'form_panel_title' => __(
                    'News %1',
                    $news->getTitle()
                ),
                'url' => $this->_adminhtmlData
                    ->getUrl('solwin_ournews/news/edit', 
                    ['news_id' => $news->getId()]),
            ];
        }

        $this->setResults($result);

        return $this;
    }
}
