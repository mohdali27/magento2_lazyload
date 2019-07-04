<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class RenameCategories extends Generic implements TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Amasty\Feed\Ui\Component\Form\GoogleTaxonomyOptions
     */
    private $googleTaxonomyOptions;

    /**
     * @var \Amasty\Feed\Model\FormFieldDependencyFactory
     */
    private $dependencyFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Amasty\Feed\Ui\Component\Form\GoogleTaxonomyOptions $googleTaxonomyOptions,
        \Amasty\Feed\Model\FormFieldDependencyFactory $dependencyFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->googleTaxonomyOptions = $googleTaxonomyOptions;
        $this->dependencyFactory = $dependencyFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Rename Categories');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Rename Categories');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
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
        /** @var \Amasty\Feed\Model\Category $model */
        $model = $this->_coreRegistry->registry('current_amfeed_category');
        /** @var \Amasty\Feed\Model\FormFieldDependency $dependency */
        $dependency = $this->dependencyFactory->create();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(\Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\General::HTML_ID_PREFIX);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Rename Categories')]);

        if ($model->getId()) {
            $fieldset->addField('feed_category_id', 'hidden', ['name' => 'feed_category_id']);
        } else {
            $model->setData('is_active', 1);
        }

        $isUseTaxonomy = $fieldset->addField(
            'use_taxonomy',
            'select',
            [
                'name' => 'use_taxonomy',
                'label' => __('Use Google Taxonomy Categories Names'),
                'title' => __('Use Google Taxonomy Categories Names'),
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $taxonomyNote = $fieldset->addField(
            'rename_note',
            'note',
            [
                'name' => 'rename_note',
                'text' => $model->getMappingNote()
            ]
        );
        $dependency->addDepend($taxonomyNote->getHtmlId(), $isUseTaxonomy->getHtmlId(), 1);

        $taxonomySource = $fieldset->addField(
            'taxonomy_source',
            'select',
            [
                'name' => 'taxonomy_source',
                'label' => __('Google Taxonomy source:'),
                'title' => __('Google Taxonomy source:'),
                'values' => $this->googleTaxonomyOptions->toOptionArray(),
                'value' => 'en-US'
            ]
        );
        $dependency->addDepend($taxonomySource->getHtmlId(), $isUseTaxonomy->getHtmlId(), 1);

        $fieldset->addField(
            'mapping',
            'text',
            [
                'name' => 'mapping',
            ]
        );

        $form->getElement(
            'mapping'
        )->setRenderer(
            $this->getLayout()->createBlock(\Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\RenameMapping::class)
        );

        $form->addValues($model->getData());
        $dependency->depend($this);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
