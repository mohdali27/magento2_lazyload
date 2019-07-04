<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Api\Data\ValidProductsInterface;
use Magento\Framework\Exception\NotFoundException;

class Preview extends Ajax
{
    /**
     * Use only one page
     */
    const PAGE = 0;

    public function execute()
    {
        $items = $this->config->getItemsForPreview() ?: 1;
        $feedId = $this->getRequest()->getParam('id');
        $response = [];

        try {
            /** @var FeedInterface $feed */
            $feed = $this->feedRepository->getById($feedId);

            //Generate random file name for preview file
            $feed->setFilename(md5(uniqid(rand(), true)));

            /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
            $searchCriteria = $this->criteriaBuilder->addFilter(
                ValidProductsInterface::FEED_ID,
                $feedId
            )->setPageSize($items)->setCurrentPage(self::PAGE + 1)->create();
            $validProducts = $this->vProductsRepository->getList($searchCriteria);
            $productCount = count($validProducts->getItems());

            if ($productCount === 0) {
                throw new NotFoundException(__('There are no products to generate feed. Please check Amasty Feed indexers status or feed conditions.'));
            }

            $response['fileType'] = $feed->getFeedType();
            $response['items'] = $productCount;
            $response['content'] = $feed->export(self::PAGE, $validProducts->getItems(), true, true);

            $feed->deleteFile();
        } catch (\Exception $exception) {
            $response['error'] = true;
            $response['message'] = $exception->getMessage();

            $this->logger->error($exception->getMessage());
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}
