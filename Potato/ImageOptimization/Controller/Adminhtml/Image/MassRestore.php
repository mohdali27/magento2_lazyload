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
 * Class MassRestore
 */
class MassRestore extends Image
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
        $this->collection = $this->filter->getCollection($this->collection);
        $count = 0;

        foreach ($this->collection->getItems() as $imageItem) {
            try {
                $image = $this->imageRepository->get($imageItem->getId());
                $this->imageManager->restoreImage($image);
                $count++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been updated.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
