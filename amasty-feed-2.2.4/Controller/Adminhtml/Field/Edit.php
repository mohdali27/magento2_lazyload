<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Amasty\Feed\Controller\Adminhtml\Field
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
        $idField = $this->getRequest()->getParam('id');
        /** @var \Amasty\Feed\Model\Field $model */
        $model = $this->cFieldsRepository->getFieldModel($idField);

        if ($idField && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This custom field no longer exists.'));

            return $this->_redirect('amfeed/*');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend($model->getName() ?: __('New Custom Field'));

        return $resultPage;
    }
}
