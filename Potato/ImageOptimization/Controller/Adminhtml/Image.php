<?php

namespace Potato\ImageOptimization\Controller\Adminhtml;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Manager\Image as ImageManager;

/**
 * Class Index
 */
abstract class Image extends Action
{
    const ADMIN_RESOURCE = 'Potato_ImageOptimization::po_image_grid';

    /** @var ImageRepositoryInterface  */
    protected $imageRepository;

    /** @var ImageManager  */
    protected $imageManager;

    /**
     * Image constructor.
     * @param Action\Context $context
     * @param ImageRepositoryInterface $imageRepository
     * @param ImageManager $imageManager
     */
    public function __construct(
        Action\Context $context,
        ImageRepositoryInterface $imageRepository,
        ImageManager $imageManager
    ) {
        parent::__construct($context);
        $this->imageRepository = $imageRepository;
        $this->imageManager = $imageManager;
    }
}
