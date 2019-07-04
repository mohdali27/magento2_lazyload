<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Image;

use Potato\ImageOptimization\Controller\Adminhtml\Image;
use Magento\Ui\Component\MassAction\Filter;
use Potato\ImageOptimization\Model\ResourceModel\Image\CollectionFactory as GridCollectionFactory;
use Potato\ImageOptimization\Model\ResourceModel\Image\Collection as GridCollection;
use Magento\Backend\App\Action;
use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Manager\Image as ImageManager;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\App;

/**
 * Class MassOptimize
 */
class MassOptimize extends Image
{
    /** @var GridCollection  */
    protected $collection;

    /** @var Filter  */
    protected $filter;

    /** @var App  */
    protected $app;

    /**
     * MassOptimize constructor.
     * @param Action\Context $context
     * @param ImageRepositoryInterface $imageRepository
     * @param ImageManager $imageManager
     * @param Filter $filter
     * @param GridCollectionFactory $collection
     * @param App $app
     */
    public function __construct(
        Action\Context $context,
        ImageRepositoryInterface $imageRepository,
        ImageManager $imageManager,
        Filter $filter,
        GridCollectionFactory $collection,
        App $app
    ) {
        parent::__construct($context, $imageRepository, $imageManager);
        $this->collection = $collection->create();
        $this->filter = $filter;
        $this->app = $app;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collection);
        $count = 0;

        foreach ($collection->getItems() as $imageItem) {
            try {
                $image = $this->imageRepository->get($imageItem->getId());
                $this->imageManager->optimizeImage($image);
                $count++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        //if selected images have status STATUS_PENDING_SERVICE, send them
        $collection->clear();
        $collection->addFieldToFilter('status', ['eq' => StatusSource::STATUS_PENDING_SERVICE]);
        try {
            $this->app->sendServiceImages($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been updated.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
