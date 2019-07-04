<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard;

use Amasty\Feed\Model\RegistryContainer;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Registry
     *
     * @var \Amasty\Feed\Model\RegistryContainer
     */
    protected $registryContainer;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        RegistryContainer $registryContainer,
        array $data = []
    ) {
        $this->registryContainer = $registryContainer;
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
        $this->_objectId = 'feed_id';
        $this->_blockGroup = 'Amasty_Feed';
        $this->_controller = 'adminhtml_googleWizard';

        parent::_construct();
        $this->buttonList->remove('reset');
        $this->buttonList->remove('back');

        $this->buttonList->add(
            'back_custom',
            [
                'label' => __('Back'),
                'class' => 'back',
                'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            ],
            10
        );

        $this->buttonList->add(
            'reset_custom',
            [
                'label' => __('Reset'),
                'class' => 'reset',
                'on_click' => 'resetForm()',
            ],
            20
        );

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'label' => __('Save and Start Generation'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form',
                            'eventData' => ['action' => ['args' => ['force_generate' => '1']]],
                        ]
                    ],
                ]
            ],
            40
        );

    }

    /**
     * Get URL for back button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('amfeed/feed');
    }
}
