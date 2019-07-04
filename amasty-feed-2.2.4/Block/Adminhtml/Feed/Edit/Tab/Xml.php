<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Xml extends Generic implements TabInterface
{
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
        return __('Content');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Content');
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

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amfeed_feed');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('xml_fieldset', ['legend' => __('XML Template')]);

        $fieldset->addField(
            'xml_header',
            'textarea',
            [
                'label' => __('Header'),
                'title' => __('Header'),
                'name' => 'xml_header'
            ]
        );

        $fieldset->addField(
            'xml_item',
            'text',
            [
                'label' => __('Item'),
                'title' => __('Item'),
                'name' => 'xml_item',
                'after_element_html' => '<small>'.__('XML Tag for Item (example for Google - item)').'</small>',
            ]
        );

        $fieldset->addField(
            'xml_content',
            'text',
            [
                'name' => 'xml_content',
                'label' => __('Content'),
                'title' => __('Content'),
                'value' => $model->getCsvField()

            ]
        );

        $form->getElement(
            'xml_content'
        )->setRenderer(
            $this->getLayout()->createBlock('Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Xml\Content')
        );

        $fieldset->addField(
            'xml_footer',
            'textarea',
            [
                'label' => __('Footer'),
                'title' => __('Footer'),
                'name' => 'xml_footer'
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
