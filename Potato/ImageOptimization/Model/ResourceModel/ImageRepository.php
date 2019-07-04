<?php

namespace Potato\ImageOptimization\Model\ResourceModel;

use Magento\Framework\Api;
use Potato\ImageOptimization\Api as ImageApi;
use Potato\ImageOptimization\Model as ImageModel;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;

/**
 * Class ImageRepository
 */
class ImageRepository implements ImageApi\ImageRepositoryInterface
{
    const PROCESS_OPTIMIZATION_IMAGE_LIMIT = 50;

    const IMAGE_TYPE_REGEXP = '/\.(png|jpe{0,1}g|gif)$/i';
    
    /**
     * @var ImageModel\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var ImageModel\ImageRegistry
     */
    protected $imageRegistry;

    /**
     * @var ImageApi\Data\ImageSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var Image
     */
    protected $imageResource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchBuilder;

    /**
     * ImageRepository constructor.
     * @param ImageModel\ImageFactory $imageFactory
     * @param ImageModel\ImageRegistry $imageRegistry
     * @param ImageApi\Data\ImageSearchResultsInterfaceFactory $searchResultsFactory
     * @param Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Image $imageResource
     * @param SearchCriteriaBuilder $searchBuilder
     */
    public function __construct(
        ImageModel\ImageFactory $imageFactory,
        ImageModel\ImageRegistry $imageRegistry,
        ImageApi\Data\ImageSearchResultsInterfaceFactory $searchResultsFactory,
        Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Image $imageResource,
        SearchCriteriaBuilder $searchBuilder
    ) {
        $this->imageFactory = $imageFactory;
        $this->imageRegistry = $imageRegistry;
        $this->imageResource = $imageResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * Create new empty image model
     * @return ImageApi\Data\ImageInterface
     */
    public function create()
    {
        return $this->imageRegistry->create();
    }
    
    /**
     * @param ImageApi\Data\ImageInterface $image
     * @return ImageApi\Data\ImageInterface
     * @throws \Exception
     */
    public function save(ImageApi\Data\ImageInterface $image)
    {
        $imageData = $this->extensibleDataObjectConverter->toNestedArray(
            $image,
            [],
            ImageApi\Data\ImageInterface::class
        );
        $imageModel = $this->imageFactory->create();
        $imageModel->addData($imageData);
        $imageModel->setId($image->getId());
        $this->imageResource->save($imageModel);
        $this->imageRegistry->push($imageModel);
        $savedObject = $this->get($imageModel->getId());
        return $savedObject;
    }

    /**
     * @param int $imageId
     *
     * @return ImageApi\Data\ImageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($imageId)
    {
        $imageModel = $this->imageRegistry->retrieve($imageId);
        return $imageModel->getDataModel();
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getByPath($path)
    {
        $imageModel = $this->imageRegistry->retrieveByPath($path);
        return $imageModel->getDataModel();
    }

    /**
     * @param ImageApi\Data\ImageInterface $image
     * @return bool
     */
    public function delete(ImageApi\Data\ImageInterface $image)
    {
        return $this->deleteById($image->getId());
    }

    /**
     * @param int $imageId
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($imageId)
    {
        $imageModel = $this->imageRegistry->retrieve($imageId);
        $imageModel->getResource()->delete($imageModel);
        $this->imageRegistry->remove($imageId);
        return true;
    }

    /**
     * @param Api\SearchCriteriaInterface $searchCriteria
     * @return ImageApi\Data\ImageSearchResultsInterface
     */
    public function getList(Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var ImageApi\Data\ImageSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        
        $collection = $this->prepareCollection($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var Api\SortOrder $sortOrder */
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $images = [];
        $searchResults->setLastPageNumber($collection->getLastPageNumber());
        foreach ($collection as $imageModel) {
            $images[] = $imageModel->getDataModel();
        }
        $searchResults->setItems($images);
        return $searchResults;
    }

    public function getCountByCriteria(Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->prepareCollection($searchCriteria);
        return $collection->getSize();
    }

    /**
     * @param Api\SearchCriteriaInterface $searchCriteria
     * @return Image\Collection
     */
    private function prepareCollection(Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Potato\ImageOptimization\Model\ResourceModel\Image\Collection $collection */
        $collection = $this->imageFactory->create()->getCollection();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        return $collection;
    }

    /**
     * @param bool $onlyCount
     * @return ImageApi\Data\ImageSearchResultsInterface
     */
    public function getAllList($onlyCount = false)
    {
        $criteria = $this->searchBuilder->create();
        if (true === $onlyCount) {
            return $this->getCountByCriteria($criteria);
        }
        return $this->getList($criteria);
    }

    /**
     * @param int $limit
     * @param int $curPage
     * @return ImageApi\Data\ImageSearchResultsInterface
     */
    public function getListPerPagination($limit, $curPage)
    {
        $criteria = $this
            ->searchBuilder
            ->setPageSize($limit)
            ->setCurrentPage($curPage)
            ->create();
        return $this->getList($criteria);
    }
    
    /**
     * @return ImageApi\Data\ImageSearchResultsInterface
     */
    public function getNeedToOptimizationList()
    {
        $criteria = $this
            ->searchBuilder
            ->addFilter(
                'status',
                [StatusSource::STATUS_PENDING , StatusSource::STATUS_OUTDATED],
                'in'
            )
            ->setPageSize(self::PROCESS_OPTIMIZATION_IMAGE_LIMIT)
            ->create();
        return $this->getList($criteria);
    }

    /**
     * @param string $status
     * @param bool $onlyCount
     * @return ImageApi\Data\ImageSearchResultsInterface
     */
    public function getListByStatus($status, $onlyCount = false)
    {
        $criteria = $this
            ->searchBuilder
            ->addFilter(
                'status',
                $status,
                'eq'
            )
            ->create();
        if (true === $onlyCount) {
            return $this->getCountByCriteria($criteria);
        }
        return $this->getList($criteria);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isPathExist($path)
    {
        $connection = $this->imageResource->getConnection();
        return (bool)$connection
            ->fetchOne('SELECT path FROM ' . $this->imageResource->getTable('potato_image_optimization_image')
                . ' WHERE path = ' . $connection->quote($path))
        ;
    }

    /**
     * @param string $imagePath
     * @return int
     */
    public function getImageType($imagePath)
    {
        if (!preg_match(self::IMAGE_TYPE_REGEXP, $imagePath) || !file_exists($imagePath)) {
            return null;
        }
        if(function_exists('mime_content_type')) {
            return mime_content_type($imagePath);
        }
        $mimeTypes = array(
            'png'  => 'image/png',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml'
        );
        $pathExtension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if (array_key_exists($pathExtension, $mimeTypes)) {
            return $mimeTypes[$pathExtension];
        }
        return null;
    }
}
