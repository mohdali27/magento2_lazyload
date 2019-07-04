<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Image;

use Potato\ImageOptimization\Controller\Adminhtml\Image;
use Magento\Ui\Component\MassAction\Filter;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\ResourceModel\Image\Grid\Collection as GridCollection;
use Magento\Backend\App\Action;
use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Manager\Image as ImageManager;

/**
 * Class MassStatus
 */
class MassStatus extends Image
{
    /** @var GridCollection  */
    protected $collection;

    /** @var Filter  */
    protected $filter;

    /**
     * MassRestore constructor.
     * @param Action\Context $context
     * @param ImageRepositoryInterface $imageRepository
     * @param ImageManager $imageManager
     * @param Filter $filter
     * @param GridCollection $collection
     */
    public function __construct(
        Action\Context $context,
        ImageRepositoryInterface $imageRepository,
        ImageManager $imageManager,
        Filter $filter,
        GridCollection $collection
    ) {
        parent::__construct($context, $imageRepository, $imageManager);
        $this->collection = $collection;
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $status = $this->getRequest()->getParam('status', null);
        if (null === $status) {
            $this->messageManager->addErrorMessage(__('Status is not found.'));
            return $resultRedirect->setPath('*/*/');
        }

        $this->collection = $this->filter->getCollection($this->collection);
        $count = 0;
        foreach ($this->collection->getItems() as $imageItem) {
            try {
                $image = $this->imageRepository->get($imageItem->getId());
                $image
                    ->setStatus($status)
                    ->setResult(__("Status has been changed"));

                if ($image->getStatus() === StatusSource::STATUS_OPTIMIZED) {
                    $image->setTime(filemtime($image->getPath()));
                }
                $this->imageRepository->save($image);
                $count++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been updated.', $count)
        );
        return $resultRedirect->setRefererUrl();
    }
}
