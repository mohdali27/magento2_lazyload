<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Api\Data;

interface ValidProductsInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const FEED_ID = 'feed_id';
    const VALID_PRODUCT_ID = 'valid_product_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $feedId
     *
     * @return \Amasty\Feed\Api\Data\ValidProductsInterface
     */
    public function setEntityId($feedId);

    /**
     * @return int
     */
    public function getFeedId();

    /**
     * @param int $feedId
     *
     * @return \Amasty\Feed\Api\Data\ValidProductsInterface
     */
    public function setFeedId($feedId);

    /**
     * @return int
     */
    public function getValidProductId();

    /**
     * @param string $validProducts
     *
     * @return \Amasty\Feed\Api\Data\ValidProductsInterface
     */
    public function setValidProductId($validProducts);
}
