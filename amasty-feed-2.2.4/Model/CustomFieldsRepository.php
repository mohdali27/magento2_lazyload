<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class CustomFieldsRepository implements \Amasty\Feed\Api\CustomFieldsRepositoryInterface
{
    /**
     * @var ConditionsFactory
     */
    private $conditionsFactory;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @var ResourceModel\Conditions
     */
    private $conditionsResource;

    /**
     * @var ResourceModel\Field
     */
    private $fieldResource;

    public function __construct(
        ConditionsFactory $conditionsFactory,
        FieldFactory $fieldFactory,
        ResourceModel\Conditions $conditionsResource,
        ResourceModel\Field $fieldResource
    ) {
        $this->conditionsFactory = $conditionsFactory;
        $this->fieldFactory = $fieldFactory;
        $this->conditionsResource = $conditionsResource;
        $this->fieldResource = $fieldResource;
    }

    /**
     * {@inheritdoc}
     */
    public function saveField($field)
    {
        try {
            $this->fieldResource->save($field);
        } catch (ValidationException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save field with code %1', $field->getCode()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveCondition($condition, $fieldId)
    {
        $this->conditionsResource->save($condition->beforeSaveCondition($fieldId));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAllConditions($fieldId, $deleteField = false)
    {
        try {
            $this->conditionsResource->deleteAllByFieldId($fieldId);

            if ($deleteField) {
                $fieldModel = $this->getFieldModel($fieldId);
                $this->fieldResource->delete($fieldModel);
            }
        } catch (ValidationException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove entity with ID%', $fieldId));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionModel($conditionId = null)
    {
        $conditionModel = $this->conditionsFactory->create();
        $this->conditionsResource->load($conditionModel, $conditionId);

        return $conditionModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsIds($fieldId)
    {
        return $this->conditionsResource->getIdsByField($fieldId);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldModel($fieldId = null)
    {
        $fieldModel = $this->fieldFactory->create();
        $this->fieldResource->load($fieldModel, $fieldId);

        return $fieldModel;
    }
}
