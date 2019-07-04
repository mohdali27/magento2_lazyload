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

class Schedule extends Generic implements TabInterface
{
    /**
     * @var \Amasty\Feed\Model\ResourceModel\Feed\Grid\ExecuteModeList
     */
    private $executeModeList;

    /**
     * @var \Amasty\Feed\Model\CronProvider
     */
    private $cronProvider;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Feed\Model\ResourceModel\Feed\Grid\ExecuteModeList $executeModeList,
        \Amasty\Feed\Model\CronProvider $cronProvider,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->executeModeList = $executeModeList;
        $this->cronProvider = $cronProvider;
    }

    public function getTabLabel()
    {
        return __('Schedule');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Schedule');
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

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Schedule')]);

        $executeMode = $fieldset->addField(
            'execute_mode',
            'select',
            [
                'label' => __('Generate feed'),
                'name' => 'execute_mode',
                'options' => $this->executeModeList->toOptionArray()
            ]
        );

        $cronDay = $fieldset->addField(
            'cron_day',
            'multiselect',
            [
                'label' => __('Day'),
                'name' => 'cron_day',
                'required' => true,
                'values' => $this->cronProvider->getOptionWeekdays()
            ]
        );

        $cronTime = $fieldset->addField(
            'cron_time',
            'multiselect',
            [
                'label' => __('Time'),
                'name' => 'cron_time',
                'required' => true,
                'values' => $this->cronProvider->getCronTime()
            ]
        );

        $form->setValues($model->getData());

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )
                ->addFieldMap(
                    $executeMode->getHtmlId(),
                    $executeMode->getName()
                )
                ->addFieldMap(
                    $cronDay->getHtmlId(),
                    $cronDay->getName()
                )
                ->addFieldMap(
                    $cronTime->getHtmlId(),
                    $cronTime->getName()
                )
                ->addFieldDependence(
                    $cronTime->getName(),
                    $executeMode->getName(),
                    'schedule'
                )
                ->addFieldDependence(
                    $cronDay->getName(),
                    $executeMode->getName(),
                    'schedule'
                )
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
