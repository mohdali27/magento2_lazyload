<?php
namespace Potato\ImageOptimization\Ui\Component\Filter\Type;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Framework\UrlInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Framework\Json\EncoderInterface as JsonEncoder;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Ui\Component\Form\Element\Select as ElementSelect;
use Magento\Ui\Component\Filters\FilterModifier;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;

/**
 * Class ErrorType
 */
class ErrorType extends Select
{
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

    /**
     * ErrorType constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param OptionSourceInterface|null $optionsProvider
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkInterfaceFactory $bookmarkDataFactory
     * @param JsonEncoder $jsonEncode
     * @param UserContextInterface $userContext
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        OptionSourceInterface $optionsProvider = null,
        BookmarkManagementInterface $bookmarkManagement,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkInterfaceFactory $bookmarkDataFactory,
        JsonEncoder $jsonEncode,
        UserContextInterface $userContext,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $filterBuilder, $filterModifier, $optionsProvider,
            $components, $data);
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkDataFactory = $bookmarkDataFactory;
        $this->jsonEncode = $jsonEncode;
        $this->userContext = $userContext;
    }

    protected function applyFilter()
    {
        parent::applyFilter();
        $bookmark = $this->getCurrentBookmark();
        $currentConfig = $bookmark->getConfig();
        if (
            !isset($this->filterData[$this->getName()])
            && !(isset($this->filterData['status']) && $this->filterData['status'] === StatusSource::STATUS_ERROR)
        ) {
            $currentConfig['current']['columns']['error_type'] = ['visible' => false, 'sortable' => false];
        } else {
            $currentConfig['current']['columns']['error_type'] = ['visible' => true, 'sortable' => true];
        }
        $bookmark->setConfig($this->jsonEncode->encode($currentConfig));
        $this->bookmarkRepository->save($bookmark);
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
