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

namespace Solwin\Ournews\Controller\Adminhtml;

abstract class News extends \Magento\Backend\App\Action
{
    /**
     * News Factory
     * 
     * @var \Solwin\Ournews\Model\NewsFactory
     */
    protected $_newsFactory;

    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * constructor
     * 
     * @param \Solwin\Ournews\Model\NewsFactory $newsFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Solwin\Ournews\Model\NewsFactory $newsFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_newsFactory = $newsFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init News
     *
     * @return \Solwin\Ournews\Model\News
     */
    protected function initNews()
    {
        $newsId  = (int) $this->getRequest()->getParam('news_id');
        /**
         * @var \Solwin\Ournews\Model\News $news
         */
        $news    = $this->_newsFactory->create();
        if ($newsId) {
            $news->load($newsId);
        }
        $this->_coreRegistry->register('solwin_ournews_news', $news);
        return $news;
    }
}