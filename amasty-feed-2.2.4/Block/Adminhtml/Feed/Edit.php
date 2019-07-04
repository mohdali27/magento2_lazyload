<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Feed;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Apply" button
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Amasty_Feed';
        $this->_controller = 'adminhtml_feed';

        parent::_construct();
    }

    /**
     * Create form block
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $model = $this->_coreRegistry->registry('current_amfeed_feed');

        $saveContinueClass = 'save';

        if ($model->getId()) {
            $this->buttonList->add(
                'save_apply',
                [
                    'class' => 'save',
                    'label' => __('Generate'),
                    'data_attribute' => [
                        'mage-init' => [
                            'Amasty_Feed/js/feed/edit' => [
                                'ajaxUrl' => $this->getUrl('*/*/ajax'),
                                'ajax' => true,
                                'stepsHtml' => $this->getChildHtml('amasty_feed_edit_tab_popup_steps'),
                                'profileTitle' => $model->getName()
                            ]
                        ]
                    ]
                ]
            );

            $this->buttonList->add(
                'preview',
                [
                    'class' => 'save',
                    'label' => __('Preview Feed'),
                    'data_attribute' => [
                        'mage-init' => [
                            'Amasty_Feed/js/feed/preview' => [
                                'ajaxUrl' => $this->getUrl('*/*/preview', ['id' => $model->getId()])
                            ]
                        ]
                    ]
                ]
            );
        } else {
            $this->buttonList->remove('save');
            $saveContinueClass = 'save primary';
        }

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => $saveContinueClass,
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );
        return parent::_prepareLayout();
    }
}
