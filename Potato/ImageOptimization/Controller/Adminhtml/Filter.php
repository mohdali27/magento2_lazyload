<?php

namespace Potato\ImageOptimization\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Framework\Json\EncoderInterface as JsonEncoder;
use Magento\Authorization\Model\UserContextInterface;
use Potato\ImageOptimization\Logger\Logger;

/**
 * Class Filter
 */
abstract class Filter extends Action
{
    const ADMIN_RESOURCE = 'Potato_ImageOptimization::po_image_grid';

    /** @var BookmarkManagementInterface  */
    protected $bookmarkManagement;

    /** @var BookmarkRepositoryInterface  */
    protected $bookmarkRepository;

    /** @var BookmarkInterfaceFactory  */
    protected $bookmarkDataFactory;

    /** @var JsonEncoder  */
    protected $jsonEncode;

    /** @var UserContextInterface  */
    protected $userContext;

    /** @var Logger  */
    protected $logger;

    /**
     * Filter constructor.
     * @param Context $context
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkInterfaceFactory $bookmarkDataFactory
     * @param JsonEncoder $jsonEncode
     * @param UserContextInterface $userContext
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        BookmarkManagementInterface $bookmarkManagement,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkInterfaceFactory $bookmarkDataFactory,
        JsonEncoder $jsonEncode,
        UserContextInterface $userContext,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkDataFactory = $bookmarkDataFactory;
        $this->jsonEncode = $jsonEncode;
        $this->userContext = $userContext;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    protected function getCurrentBookmark()
    {
        $identifier = 'current';
        $namespace = 'image_listing';
        /** @var \Magento\Ui\Api\Data\BookmarkInterface $currentBookmark */
        $currentBookmark = $this->bookmarkManagement->getByIdentifierNamespace($identifier, $namespace);
        if (!$currentBookmark) {
            $currentBookmark = $this->bookmarkDataFactory->create();
            $currentBookmark
                ->setIdentifier($identifier)
                ->setNamespace($namespace)
                ->setUserId($this->userContext->getUserId());
            ;
        }
        return $currentBookmark;
    }
}
