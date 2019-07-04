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

class ExcludeCategories extends Generic implements TabInterface
{
    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Exclude Categories');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Exclude Categories');
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

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(\Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\General::HTML_ID_PREFIX);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Exclude Categories')]);

        if ($model->getId()) {
            $fieldset->addField('feed_category_id', 'hidden', ['name' => 'feed_category_id']);
        } else {
            $model->setData('is_active', 1);
        }

        $fieldset->addField(
            'exclude_note',
            'note',
            [
                'name' => 'exclude_note',
                'text' => $model->getExcludeNote()
            ]
        );

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
            $this->getLayout()->createBlock(\Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\ExcludeMapping::class)
        );

        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
