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

namespace Solwin\Ournews\Block\Adminhtml\News\Edit\Tab;

class Content extends \Magento\Backend\Block\Widget\Form\Generic
implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * Wysiwyg config
     * 
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * Status options
     * 
     * @var \Solwin\Ournews\Model\News\Source\IsActive
     */
    protected $_isActiveOptions;

    /**
     * constructor
     * 
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Solwin\Ournews\Model\News\Source\IsActive $isActiveOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Solwin\Ournews\Model\News\Source\IsActive $isActiveOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig   = $wysiwygConfig;
        $this->_isActiveOptions = $isActiveOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Solwin\Ournews\Model\News $news */
        $news = $this->_coreRegistry->registry('solwin_ournews_news');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('news_');
        $form->setFieldNameSuffix('news');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Content'),
                'class'  => 'fieldset-wide'
            ]
        );
       
        $fieldset->addField(
            'shortdesc',
            'editor',
            [
                'name'  => 'shortdesc',
                'label' => __('Short Description'),
                'title' => __('Short Description'),
                'required' => true,
                'config'    => $this->_wysiwygConfig->getConfig()
            ]
        );
        
        $fieldset->addField(
            'description',
            'editor',
            [
                'name'  => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'config'    => $this->_wysiwygConfig->getConfig()
            ]
        );
        

        $newsData = $this->_session->getData('solwin_ournews_news_data', true);
        if ($newsData) {
            $news->addData($newsData);
        } else {
            if (!$news->getId()) {
                $news->addData($news->getDefaultValues());
            }
        }
        $form->addValues($news->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Content');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}