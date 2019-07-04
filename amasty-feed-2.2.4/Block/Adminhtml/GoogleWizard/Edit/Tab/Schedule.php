<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab;

class Schedule extends TabGeneric
{
    /**
     * @var \Amasty\Feed\Model\FeedFactory
     */
    private $feedFactory;

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Feed\Grid\ExecuteModeList
     */
    private $executeModeList;

    /**
     * @var \Amasty\Feed\Model\CronProvider
     */
    private $cronProvider;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        \Amasty\Feed\Model\FeedFactory $feedFactory,
        \Amasty\Feed\Model\ResourceModel\Feed\Grid\ExecuteModeList $executeModeList,
        \Amasty\Feed\Model\CronProvider $cronProvider,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $registryContainer, $data);
        $this->feedFactory = $feedFactory;
        $this->executeModeList = $executeModeList;
        $this->cronProvider = $cronProvider;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Step 6: Schedule Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Step 6: Schedule Settings');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareNotEmptyForm()
    {
        $feedId = $this->_request->getParam('amfeed_id');
        /** @var \Amasty\Feed\Model\Feed $model */
        $model = $this->feedFactory->create();

        if ($feedId) {
            $model->loadByFeedId($feedId);
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('schedule_fieldset', ['legend' => __('Schedule')]);

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

        return $this;
    }
}
