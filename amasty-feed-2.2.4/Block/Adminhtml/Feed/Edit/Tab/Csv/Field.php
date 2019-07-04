<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Csv;

class Field extends \Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Content
{
    protected $_template = 'feed/csv.phtml';

    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label'   => __('Add Attribute'),
                'onclick' => 'return fieldControl.addItem();',
                'class'   => 'add'
            ]
        );

        $button->setName('add_attribute_button');

        $this->setChild('add_attribute_button', $button);

        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label'   => __('Add Static Text'),
                'onclick' => 'return fieldControl.addStaticTextItem();',
                'class'   => 'add'
            ]
        );

        $button->setName('add_static_text_button');

        $this->setChild('add_static_text_button', $button);

        return parent::_prepareLayout();
    }

    public function getAddAttributeButtonHtml()
    {
        return $this->getChildHtml('add_attribute_button');
    }

    public function getAddStaticTextButtonHtml()
    {
        return $this->getChildHtml('add_static_text_button');
    }
}
