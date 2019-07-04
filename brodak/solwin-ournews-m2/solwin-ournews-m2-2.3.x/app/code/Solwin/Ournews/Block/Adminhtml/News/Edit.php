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

namespace Solwin\Ournews\Block\Adminhtml\News;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * constructor
     * 
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize News edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'news_id';
        $this->_blockGroup = 'Solwin_Ournews';
        $this->_controller = 'adminhtml_news';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save News Details'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete News Details'));
    }
    /**
     * Retrieve text for header element depending on loaded News
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Solwin\Ournews\Model\News $news */
        $news = $this->_coreRegistry->registry('solwin_ournews_news');
        if ($news->getId()) {
            return __("Edit News '%1'", $this->escapeHtml($news->getTitle()));
        }
        return __('New News Details');
    }
}