<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

use \Amasty\Feed\Block\Adminhtml\Field\Edit\Conditions;

class Save extends \Amasty\Feed\Controller\Adminhtml\Field
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\Feed\Api\CustomFieldsRepositoryInterface
     */
    private $cFieldsRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Amasty\Feed\Api\CustomFieldsRepositoryInterface $cFieldsRepository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->cFieldsRepository = $cFieldsRepository;

        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Amasty\Feed\Model\Field $model */
        $model = $this->cFieldsRepository->getFieldModel();

        if ($data) {
            $model->setData($data);

            try {
                $this->cFieldsRepository->saveField($model);

                $this->cFieldsRepository->deleteAllConditions($model->getId());

                $this->saveCondition($data, $model->getId());
                $this->saveCondition($data, $model->getId(), 'default');

                $this->messageManager->addSuccessMessage(__('Saved successfully'));
                $this->dataPersistor->clear(Conditions::FORM_NAMESPACE);

                if (!$this->getRequest()->getParam('back')) {
                    return $this->_redirect('amfeed/*/');
                }
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('Custom Field with this code are exist. Please, choose another code name.'));

                $this->dataPersistor->set(Conditions::FORM_NAMESPACE, $data);

                if (!isset($data['feed_field_id'])) {
                    return $this->_redirect('amfeed/*/new');
                }
            }
        }

        return $this->_redirect('amfeed/field/edit', ['id' => $model->getId()]);
    }

    /**
     * Save condition block
     * Default block should be saved last
     *
     * @param array $data
     * @param string $block
     */
    private function saveCondition(&$data, $feedId, $block = 'rule')
    {
        $model = $this->cFieldsRepository->getConditionModel();

        if (isset($data[$block])) {
            $model->loadPost($data[$block]);
        }

        $this->cFieldsRepository->saveCondition($model, $feedId);
    }
}
