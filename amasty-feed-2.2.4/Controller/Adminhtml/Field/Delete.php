<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

class Delete extends \Amasty\Feed\Controller\Adminhtml\Field
{
    /**
     * @var \Amasty\Feed\Api\CustomFieldsRepositoryInterface
     */
    private $cFieldsRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Feed\Api\CustomFieldsRepositoryInterface $cFieldsRepository
    ) {
        $this->cFieldsRepository = $cFieldsRepository;

        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $this->cFieldsRepository->deleteAllConditions($id, true);

                $this->messageManager->addSuccessMessage(__('You deleted the field.'));

                return $this->_redirect('amfeed/*/');
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __('We can\'t delete the field right now. Please review the log and try again.')
                );

                return $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a field to delete.'));

        return $this->_redirect('amfeed/*/');
    }
}
