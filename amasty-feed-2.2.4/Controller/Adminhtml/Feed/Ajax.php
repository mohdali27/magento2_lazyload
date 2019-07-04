<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Model\Feed;

class Ajax extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    private $urlFactory;

    /**
     * @var \Amasty\Feed\Api\ValidProductsRepositoryInterface
     */
    protected $vProductsRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var \Amasty\Feed\Api\FeedRepositoryInterface
     */
    protected $feedRepository;

    /**
     * @var \Amasty\Feed\Model\Config
     */
    protected $config;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Amasty\Feed\Api\ValidProductsRepositoryInterface $vProductsRepository,
        \Amasty\Feed\Api\FeedRepositoryInterface $feedRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Amasty\Feed\Model\Config $config
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlFactory = $urlFactory;
        $this->vProductsRepository = $vProductsRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->feedRepository = $feedRepository;
        $this->config = $config;

        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $logger);
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    private function getUrlInstance()
    {
        return $this->urlFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $page = (int)$this->getRequest()->getParam('page', 0);
        $feedId = $this->getRequest()->getParam('feed_entity_id');
        $body = [];
        $feed = null;
        $currentPage = $page + 1; // Valid page for searchCriteria

        try {
            $itemsPerPage = (int)$this->config->getItemsPerPage();
            $lastPage = false;
            /** @var FeedInterface $feed */
            $feed = $this->feedRepository->getById($feedId);

            $feed->setGenerationType(Feed::MANUAL_GENERATED);

            if ($page === 0) {
                $feed->setProductsAmount(0);
            }

            /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
            $searchCriteria = $this->criteriaBuilder->addFilter(
                ValidProductsInterface::FEED_ID,
                $feedId
            )
                ->setPageSize($itemsPerPage)
                ->setCurrentPage($currentPage)
                ->create();
            $validProducts = $this->vProductsRepository->getList($searchCriteria);
            $totalPages = ceil($validProducts->getTotalCount() / $itemsPerPage);

            if ((int)$page == $totalPages - 1 || $totalPages == 0) {
                $lastPage = true;
            }

            $productItems = $validProducts->getItems();
            $feed->export($page, $productItems, $lastPage);

            if ($lastPage) {
                $feed->compress();
            }

            $body['exported'] = count($productItems);
            $body['isLastPage'] = $lastPage;
            $body['total'] = $validProducts->getTotalCount();
        } catch (\Exception $e) {
            $this->logger->critical($e);

            $feed->setStatus(Feed::FAILED);
            $this->feedRepository->save($feed);

            $body['error'] = $e->getMessage();
        }

        if (!isset($body['error'])) {
            $urlInstance = $this->getUrlInstance();

            $routeParams = [
                '_direct' => 'amfeed/feed/download',
                '_query' => [
                    'id' => $feed->getEntityId()
                ]
            ];

            $href = $urlInstance
                ->setScope($feed->getStoreId())
                ->getUrl(
                    '',
                    $routeParams
                );

            $body['download'] = $href;
        } else {
            $body['error'] = substr($body['error'], 0, 150) . '...';
        }

        return $this->resultJsonFactory->create()->setData($body);
    }
}
