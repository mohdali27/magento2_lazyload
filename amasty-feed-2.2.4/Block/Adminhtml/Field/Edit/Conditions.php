<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Field\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Conditions extends Generic
{
    /**#@+
     * Keys for DataPersistor and UI
     */
    const FORM_NAMESPACE = 'amfeed_field_form';

    const CONDITION_IDS = 'amdeed_feed_field_condition_ids';
    /**#@-*/

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditionsBlock;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    private $fieldset;

    /**
     * @var \Amasty\Feed\Model\Conditions
     */
    private $condition;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\Feed\Ui\Component\Form\ProductAttributeOptions
     */
    private $attributeOptions;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metadata;

    /**
     * @var \Amasty\Feed\Api\CustomFieldsRepositoryInterface
     */
    private $cFieldsRepository;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $fieldset,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        \Amasty\Feed\Ui\Component\Form\ProductAttributeOptions $attributeOptions,
        \Amasty\Feed\Api\CustomFieldsRepositoryInterface $cFieldsRepository,
        array $data = []
    ) {
        $this->conditionsBlock = $conditions;
        $this->formFactory = $formFactory;
        $this->fieldset = $fieldset;
        $this->metadata = $metadata;
        $this->dataPersistor = $dataPersistor;
        $this->attributeOptions = $attributeOptions;
        $this->cFieldsRepository = $cFieldsRepository;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function toHtml()
    {
        $this->getConditionModel();

        if (version_compare($this->metadata->getVersion(), '2.2.0', '>=')) {
            //Fix for Magento >2.2.0 to display right form layout.
            //Result of compatibility with 2.1.x.
            $this->_prepareLayout();
        }

        $conditionsFieldSetId = $this->condition->getConditionsFieldSetId(self::FORM_NAMESPACE);
        $newChildUrl = $this->getUrl(
            'sales_rule/promo_quote/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => self::FORM_NAMESPACE]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create();
        $renderer = $this->fieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            $conditionsFieldSetId,
            []
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions' . $conditionsFieldSetId,
            'text',
            [
                'name' => 'conditions' . $conditionsFieldSetId,
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => self::FORM_NAMESPACE,
            ]
        )->setRule($this->condition)->setRenderer($this->conditionsBlock);

        $fieldset = $form->addFieldset('result', ['legend' => __('Output Value')]);

        $fieldset->addField(
            'result[attribute]',
            'select',
            [
                'name' => 'rule[result][attribute]',
                'label' => __('Attribute'),
                'title' => __('Attribute'),
                'options' => $this->attributeOptions->getOptionsForBlock(),
                'data-form-part' => self::FORM_NAMESPACE,
            ]
        );

        $fieldset->addField(
            'result[modify]',
            'text',
            [
                'name' => 'rule[result][modify]',
                'label' => __('Modification'),
                'title' => __('Modification'),
                'placeholder' => __('Percentage (like +15%), or fixed value (like -20)'),
                'data-form-part' => self::FORM_NAMESPACE,
            ]
        );

        $form->setValues($this->condition->getData());
        $this->setConditionFormName($this->condition->getConditions(), self::FORM_NAMESPACE);

        return $form->toHtml();
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     *
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName)
    {
        $conditions->setFormName($formName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }

    private function getConditionModel()
    {
        if ($this->restoreUnsavedData()) {
            return;
        }

        $this->prepareCondition();

        $this->prepareResultData();
    }

    private function prepareResultData()
    {
        foreach ($this->condition->getFieldResult() as $key => $value) {
            $key = 'result[' . $key . ']';
            $this->condition->setData($key, $value);
        }
    }

    private function prepareCondition()
    {
        $resultId = null;
        $idField = $this->getRequest()->getParam('id');
        $actualConditions = $this->dataPersistor->get(self::CONDITION_IDS);

        if ($actualConditions) {
            $resultId = array_shift($actualConditions);
            $this->dataPersistor->set(self::CONDITION_IDS, $actualConditions);
        } else {
            $ids = $this->cFieldsRepository->getConditionsIds($idField);
            array_pop($ids);
            $resultId = array_shift($ids);
            $this->dataPersistor->set(self::CONDITION_IDS, $ids);
        }

        $this->condition = $this->cFieldsRepository->getConditionModel($resultId);
    }

    /**
     * Try to get unsaved data if error was occurred.
     * Return true if the data was received.
     *
     * @return bool
     */
    private function restoreUnsavedData()
    {
        $this->condition = $this->cFieldsRepository->getConditionModel();
        $tempData = $this->dataPersistor->get(self::FORM_NAMESPACE);

        if ($tempData) {
            $tempData['rule'] = isset($tempData['rule']) ? $tempData['rule'] : [];
            $this->condition->loadPost($tempData['rule']);
            $this->condition->getConditions();
            $this->prepareResultData();

            return true;
        }

        return false;
    }
}
