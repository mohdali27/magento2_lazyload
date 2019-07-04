<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Analytics extends Generic implements TabInterface{
    
    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Analytics');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Analytics');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amfeed_feed');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fldInfo = $form->addFieldset('analytics_fieldset', ['legend' => __('Google Analytics')]);

        $fldInfo->addField('utm_source', 'text', [
            'label'     => __('Campaign Source'),
            'name'      => 'utm_source',
            'note' => __('<b>Required.</b> Use <b>utm_source</b> to identify a search engine, newsletter name, or other source.<br/><i>Example:</i> google')
        ]);
        
        $fldInfo->addField('utm_medium', 'text', [
            'label'     => __('Campaign Medium'),
            'name'      => 'utm_medium',
            'note' => __('<b>Required.</b> Use <b>utm_medium</b> to identify a medium such as email or cost-per- click<br/><i>Example:</i> cpc')
        ]);
        
        $fldInfo->addField('utm_term', 'text', [
            'label'     => __('Campaign Term'),
            'name'      => 'utm_term',
            'note' => __('Used for paid search. Use <b>utm_term</b> to note the keywords for this ad.<br/><i>Example:</i> running+shoes')
        ]);
        
        $fldInfo->addField('utm_content', 'text', [
            'label'     => __('Campaign Content'),
            'name'      => 'utm_content',
            'note' => __('Used for A/B testing and content-targeted ads. Use <b>utm_content</b> to differentiate ads or links that point to the same URL.<br/><i>Example:</i> logolink <i>or</i> textlink')
        ]);
        
        $fldInfo->addField('utm_campaign', 'text', [
            'label'     => __('Campaign Name'),
            'name'      => 'utm_campaign',
            'note' => __('Used for keyword analysis. Use <b>utm_campaign</b> to identify a specific product promotion or strategic campaign.<br/><i>Example:</i> spring_sale')
        ]);
        
        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
