<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Amasty\Feed\Api\Data;
use Amasty\Feed\Api\ScheduleRepositoryInterface;
use Amasty\Feed\Model\ResourceModel\Schedule as ScheduleResourceModel;
use Amasty\Feed\Model\ScheduleFactory;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    /**
     * @var ScheduleResourceModel
     */
    private $scheduleResource;

    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    public function __construct(
        ScheduleResourceModel $scheduleResource,
        ScheduleFactory $scheduleFactory
    ) {
        $this->scheduleResource = $scheduleResource;
        $this->scheduleFactory = $scheduleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\ScheduleInterface $scheduleModel)
    {
        try {
            $this->scheduleResource->save($scheduleModel);
        } catch (ValidationException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save model %1', $scheduleModel->getId()));
        }

        return $scheduleModel;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        /** @var \Amasty\Feed\Model\Schedule $scheduleModel */
        $scheduleModel = $this->scheduleFactory->create();
        $this->scheduleResource->load($scheduleModel, $id);

        if (!$scheduleModel->getId()) {
            throw new NoSuchEntityException(__('Entity with specified ID "%1" not found.', $id));
        }

        return $scheduleModel;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\ScheduleInterface $scheduleModel)
    {
        try {
            $this->scheduleResource->delete($scheduleModel);
        } catch (ValidationException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove entity with ID "%1"', $scheduleModel->getId()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        $model = $this->get($id);
        $this->delete($model);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByFeedId($feedId)
    {
        try {
            $this->scheduleResource->deleteByFeedId($feedId);
        } catch (ValidationException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove entities with Feed ID "%1"', $feedId));
        }

        return true;
    }
}
