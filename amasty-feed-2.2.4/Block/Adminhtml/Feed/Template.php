<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Block\Adminhtml\Feed;

use Amasty\Feed\Model\Feed;

class Template extends \Magento\Backend\Block\Widget\Container
{
    protected $_systemStore;
    protected $_formFactory;
    protected $_feed;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        Feed $feed,
        array $data = []
    ) {
        $this->_feed = $feed;

        parent::__construct($context, $data);

        $this->_addSetupGoogleFeedButton();
        $this->_addNewButton();
    }

    /**
     * Add setup google wizard button
     *
     * @return $this
     */
    protected function _addSetupGoogleFeedButton()
    {
        $this->addButton(
            'googleFeed',
            [
                'label'   => __("Setup Google Feed"),
                'class'   => 'google-feed primary',
                'onclick' => 'setLocation(\'' . $this->getCreateGoogleFeedUrl()
                    . '\')'
            ]
        );

        return $this;
    }

    /**
     * Add new feed button
     *
     * @return $this
     */
    protected function _addNewButton()
    {
        $this->addButton(
            'add',
            [
                'label' => __("Add New Feed"),
                'class' => 'add primary',
                'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                'options' => $this->_getOptions()
            ]
        );

        return $this;
    }

    /**
     * Get options
     *
     * @return array
     */
    protected function _getOptions()
    {
        $options = [
            [
                'label' => __('Custom Feed'),
                'onclick' => 'setLocation(\'' . $this->getCreateUrl() . '\')',
                'default' => true,
            ]
        ];
        foreach($this->_feed->getTemplateOptionHash() as $id => $label){
            $options[] = [
                'label' => __('Add %1 Template', $label),
                'onclick' => "setLocation('" . $this->getUrl('*/*/fromTemplate', [
                        'id' => $id
                ]) . "')",
            ];
        }

        return $options;
    }

    /**
     * Get new url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    /**
     * Get google feed url
     *
     * @return string
     */
    public function getCreateGoogleFeedUrl()
    {
        return $this->getUrl('*/googleWizard/index');
    }
}
