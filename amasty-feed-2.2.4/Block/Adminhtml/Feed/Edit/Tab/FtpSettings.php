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

class FtpSettings extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Amasty\Feed\Model\FormFieldDependencyFactory
     */
    private $dependencyFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Amasty\Feed\Model\FormFieldDependencyFactory $dependencyFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->dependencyFactory = $dependencyFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('FTP Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('FTP Settings');
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
        /** @var \Amasty\Feed\Model\FormFieldDependency $dependency */
        $dependency = $this->dependencyFactory->create();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('delivery_fieldset', ['legend' => __('FTP Settings')]);

        $enabledSelect = $fieldset->addField(
            'delivery_enabled',
            'select',
            [
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'name' => 'delivery_enabled',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $fieldset->addField(
            'delivery_host',
            'text',
            [
                'name' => 'delivery_host',
                'label' => __('Host'),
                'title' => __('Host'),
                'required' => true,
                'note' => '<small>' . __('Add port if necessary (example.com:321)') . '</small>'
            ]
        );

        $typeSelect = $fieldset->addField(
            'delivery_type',
            'select',
            [
                'label' => __('Protocol'),
                'title' => __('Protocol'),
                'name' => 'delivery_type',
                'options' => [
                    'ftp' => __('FTP'),
                    'sftp' => __('SFTP')
                ],
            ]
        );

        $fieldset->addField(
            'delivery_user',
            'text',
            [
                'name' => 'delivery_user',
                'label' => __('User'),
                'title' => __('User'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'delivery_password',
            'password',
            [
                'name' => 'delivery_password',
                'label' => __('Password'),
                'title' => __('Password'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'delivery_path',
            'text',
            [
                'name' => 'delivery_path',
                'label' => __('Path'),
                'title' => __('Path'),
                'required' => true
            ]
        );

        $modeSelect = $fieldset->addField(
            'delivery_passive_mode',
            'select',
            [
                'label' => __('Passive Mode'),
                'title' => __('Passive Mode'),
                'name' => 'delivery_passive_mode',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $button = $fieldset->addField(
            'button',
            'button',
            []
        );
        $button->setRenderer(
            $this->getLayout()->createBlock('\Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Buttons\TestConnection')
        );

        foreach ($fieldset->getChildren() as $element) {
            if ($element->getHtmlId() !== $enabledSelect->getHtmlId()) {
                $dependency->addDepend($element->getHtmlId(), $enabledSelect->getHtmlId(), '1');
            }
        }
        $dependency->addDepend($modeSelect->getHtmlId(), $typeSelect->getHtmlId(), 'ftp');
        $dependency->depend($this);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
