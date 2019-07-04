<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\LowStockNotification;

use Magestore\InventorySuccess\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\ValidatorException;

class RuleRepository implements \Magestore\InventorySuccess\Api\LowStockNotification\RuleRepositoryInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule
     */
    protected $ruleResource;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * RuleRepository constructor.
     * @param \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule $ruleResource
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule $ruleResource,
        \Magestore\InventorySuccess\Model\LowStockNotification\RuleFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\LowStockNotification\RuleInterface $rule)
    {
        if ($rule->getRuleId()) {
            $rule = $this->get($rule->getRuleId())->addData($rule->getData());
        }

        try {
            $this->ruleResource->save($rule);
            unset($this->rules[$rule->getId()]);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save rule %1', $rule->getRuleId()));
        }
        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function get($ruleId)
    {
        if (!isset($this->rules[$ruleId])) {
            /** @var \Magento\CatalogRule\Model\Rule $rule */
            $rule = $this->ruleFactory->create();

            /* TODO: change to resource model after entity manager will be fixed */
            $rule->load($ruleId);
            if (!$rule->getRuleId()) {
                throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $ruleId));
            }
            $this->rules[$ruleId] = $rule;
        }
        return $this->rules[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\LowStockNotification\RuleInterface $rule)
    {
        try {
            $this->ruleResource->delete($rule);
            unset($this->rules[$rule->getId()]);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove rule %1', $rule->getRuleId()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        $model = $this->get($ruleId);
        $this->delete($model);
        return true;
    }
}
