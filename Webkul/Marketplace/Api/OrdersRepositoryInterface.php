<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Api;

/**
 * Orders CRUD interface.
 */
interface OrdersRepositoryInterface
{
    /**
     * Retrieve seller order by id.
     *
     * @api
     * @param string $id
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve all seller order by seller id.
     *
     * @api
     * @param int $sellerId
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBySellerId($sellerId);

    /**
     * Retrieve order by order id.
     *
     * @api
     * @param int orderId
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByOrderId($orderId);

    /**
     * Retrieve all seller order.
     *
     * @api
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function getList();
}
