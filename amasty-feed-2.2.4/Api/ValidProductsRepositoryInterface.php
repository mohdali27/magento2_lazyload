<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Api;

interface ValidProductsRepositoryInterface
{
    /**
     * Save valid product ids.
     *
     * @param \Amasty\Feed\Api\Data\ValidProductsInterface $validProducts
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Amasty\Feed\Api\Data\ValidProductsInterface $validProducts);

    /**
     * Retrieve valid product ids
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Ui\Api\Data\BookmarkSearchResultsInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete valid products ids
     *
     * @param \Amasty\Feed\Api\Data\ValidProductsInterface $validProducts
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Amasty\Feed\Api\Data\ValidProductsInterface $validProducts);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\Feed\Api\Data\ValidProductsInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);
}
