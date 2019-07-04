<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends Delete
{
    /**
     * @var \Amasty\Feed\Model\ResourceModel\Field\Collection
     */
    private $collection;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Amasty\Feed\Api\CustomFieldsRepositoryInterface
     */
    private $cFieldsRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\Feed\Api\CustomFieldsRepositoryInterface $cFieldsRepository,
        \Amasty\Feed\Model\ResourceModel\Field\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->cFieldsRepository = $cFieldsRepository;
        $this->collection = $collectionFactory->create();

        parent::__construct($context, $cFieldsRepository);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collection);
        $recordDeleted = 0;

        /** @var \Amasty\Feed\Model\Field $record */
        foreach ($collection->getItems() as $record) {
            $this->cFieldsRepository->deleteAllConditions($record->getId(), true);
            $recordDeleted++;
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $recordDeleted));

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
