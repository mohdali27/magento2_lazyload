<?php

namespace Potato\ImageOptimization\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Potato\ImageOptimization\Api\Data\ImageInterfaceFactory;

/**
 * Class ImageRegistry
 */
class ImageRegistry
{
    /** @var ImageFactory  */
    private $imageFactory;

    /** @var array  */
    private $imageRegistryById = [];

    /** @var array  */
    private $imageRegistryByPath = [];

    /** @var ResourceModel\Image  */
    private $imageResource;

    /** @var ImageInterfaceFactory  */
    private $dataFactory;

    /**
     * ImageRegistry constructor.
     * @param ImageFactory $imageFactory
     * @param ResourceModel\Image $imageResource
     * @param ImageInterfaceFactory $dataFactory
     */
    public function __construct(
        ImageFactory $imageFactory,
        ResourceModel\Image $imageResource,
        ImageInterfaceFactory $dataFactory
    ) {
        $this->imageResource = $imageResource;
        $this->imageFactory = $imageFactory;
        $this->dataFactory = $dataFactory;
    }

    /**
     * @param int $imageId
     * @return Image
     * @throws NoSuchEntityException
     */
    public function retrieve($imageId)
    {
        if (!isset($this->imageRegistryById[$imageId])) {
            /** @var Image $image */
            $image = $this->imageFactory->create();
            $this->imageResource->load($image, $imageId);
            if (!$image->getId()) {
                throw NoSuchEntityException::singleField('imageId', $imageId);
            } else {
                $this->imageRegistryById[$imageId] = $image;
            }
        }
        return $this->imageRegistryById[$imageId];
    }

    /**
     * @param string $path
     * @return Image
     * @throws NoSuchEntityException
     */
    public function retrieveByPath($path)
    {
        if (!isset($this->imageRegistryByPath[$path])) {
            /** @var Image $image */
            $image = $this->imageFactory->create();
            $this->imageResource->load($image, $path, 'path');
            if (!$image->getId()) {
                throw NoSuchEntityException::singleField('path', $path);
            } else {
                $this->imageRegistryByPath[$path] = $image;
            }
        }
        return $this->imageRegistryByPath[$path];
    }

    /**
     * @param int $imageId
     * @return void
     */
    public function remove($imageId)
    {
        if (isset($this->imageRegistryById[$imageId])) {
            unset($this->imageRegistryById[$imageId]);
        }
    }

    /**
     * @param Image $image
     * @return $this
     */
    public function push(Image $image)
    {
        $this->imageRegistryById[$image->getId()] = $image;
        return $this;
    }

    /**
     * @return \Potato\ImageOptimization\Api\Data\ImageInterface
     */
    public function create()
    {
        return $this->dataFactory->create();
    }
}
