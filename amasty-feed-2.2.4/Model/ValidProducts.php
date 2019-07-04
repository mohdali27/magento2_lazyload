<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Amasty\Feed\Api\Data\ValidProductsInterface;

class ValidProducts extends \Magento\Framework\Model\AbstractModel implements ValidProductsInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Amasty\Feed\Model\ResourceModel\ValidProducts::class);
        $this->setIdFieldName(ValidProductsInterface::ENTITY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getFeedId()
    {
        return $this->_getData(ValidProductsInterface::FEED_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFeedId($feedId)
    {
        return $this->setData(ValidProductsInterface::FEED_ID, $feedId);
    }

    /**
     * {@inheritdoc}
     */
    public function getValidProductId()
    {
        return $this->_getData(ValidProductsInterface::VALID_PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setValidProductId($validProducts)
    {
        return $this->setData(ValidProductsInterface::VALID_PRODUCT_ID, $validProducts);
    }
}
