<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\AdjustStock;

use Magestore\InventorySuccess\Api\AdjustStock\AdjustStockRepositoryInterface;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;
use \Magestore\InventorySuccess\Api\Data\AdjustStock\CreateAdjustStockRequestInterface;

class AdjustStockRepository implements AdjustStockRepositoryInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magestore\InventorySuccess\Api\Helper\SystemInterface
     */
    protected $systemHelper;

    /**
     * @var \Magestore\InventorySuccess\Model\AdjustStockFactory
     */
    protected $adjustStockFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface
     */
    protected $adjustStockManagement;

    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\User\Model\User
     */
    protected $userFactory;

    /**
     * AdjustStockRepository constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement
     * @param \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper,
        \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory,
        \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement,
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger,
        \Magento\User\Model\User $userFactory
    )
    {
        $this->objectManager = $objectManager;
        $this->systemHelper = $systemHelper;
        $this->adjustStockFactory = $adjustStockFactory;
        $this->adjustStockManagement = $adjustStockManagement;
        $this->logger = $logger;
        $this->userFactory = $userFactory;
    }

    /**
     * @inheritDoc
     */
    public function get($adjustStockCode)
    {
        /**
         * @var $adjustStockModel \Magestore\InventorySuccess\Model\AdjustStock
         */
        $adjustStockModel = $this->adjustStockFactory->create();
        $adjustStockModel->getResource()->load($adjustStockModel, $adjustStockCode, 'adjuststock_code');
        return $adjustStockModel;
    }

    /**
     * @inheritdoc
     */
    public function createAdjustStock($adjustStockRequest)
    {
        /** @var \Magestore\InventorySuccess\Model\AdjustStock $adjustStock */
        try {
            $adjustStock = $this->adjustStockFactory->create();
            if ($adjustStockRequest->getAdjustStockId()) {
                $adjustStock->getResource()->load($adjustStock, $adjustStockRequest->getAdjustStockId());
                if ($adjustStock->getAdjustStockId()) {
                    if ($adjustStock->getStatus() == AdjustStockInterface::STATUS_COMPLETED || $adjustStock->getStatus() == AdjustStockInterface::STATUS_CANCELED) {
                        return $adjustStock;
                    }
                }
            }
            $adjustData = $this->getAdjustData($adjustStockRequest);
            $adjustData['products'] = $this->getProducts($adjustStockRequest->getProducts());
            $this->adjustStockManagement->createAdjustment($adjustStock, $adjustData);

            if ($adjustStock->getId()) {
                if ($adjustStockRequest->getAction() == 'complete') {
                    $this->adjustStockManagement->complete($adjustStock);
                }
            }
            return $adjustStock;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\AlreadyExistsException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * get adjust stock data
     *
     * @param array $data
     * @return array
     */
    protected function getAdjustData($data)
    {
        $adjustData = [];
        $adjustData[CreateAdjustStockRequestInterface::ADJUSTSTOCK_CODE] = isset($data[CreateAdjustStockRequestInterface::ADJUSTSTOCK_CODE]) ?
            $data[CreateAdjustStockRequestInterface::ADJUSTSTOCK_CODE] :
            null;
        $adjustData[CreateAdjustStockRequestInterface::WAREHOUSE_ID] = isset($data[CreateAdjustStockRequestInterface::WAREHOUSE_ID]) ?
            $data[CreateAdjustStockRequestInterface::WAREHOUSE_ID] :
            null;
        $adjustData[CreateAdjustStockRequestInterface::WAREHOUSE_CODE] = isset($data[CreateAdjustStockRequestInterface::WAREHOUSE_CODE]) ?
            $data[CreateAdjustStockRequestInterface::WAREHOUSE_CODE] :
            null;
        $adjustData[CreateAdjustStockRequestInterface::WAREHOUSE_NAME] = isset($data[CreateAdjustStockRequestInterface::WAREHOUSE_NAME]) ?
            $data[CreateAdjustStockRequestInterface::WAREHOUSE_NAME] :
            null;
        $adjustData[CreateAdjustStockRequestInterface::REASON] = isset($data[CreateAdjustStockRequestInterface::REASON]) ?
            $data[CreateAdjustStockRequestInterface::REASON] :
            '';
        $adjustData[CreateAdjustStockRequestInterface::CREATED_AT] = isset($data[CreateAdjustStockRequestInterface::CREATED_AT]) ?
            $data[CreateAdjustStockRequestInterface::CREATED_AT] :
            $this->systemHelper->getCurTime();
        $adjustData[CreateAdjustStockRequestInterface::CREATED_BY] = isset($data[CreateAdjustStockRequestInterface::CREATED_BY]) ?
            $data[CreateAdjustStockRequestInterface::CREATED_BY] :
            $this->getDefaultAdmin();
        $adjustData[CreateAdjustStockRequestInterface::CONFIRMED_BY] = isset($data[CreateAdjustStockRequestInterface::CONFIRMED_BY]) ?
            $data[CreateAdjustStockRequestInterface::CONFIRMED_BY] :
            $this->getDefaultAdmin();

        $adjustData[CreateAdjustStockRequestInterface::ACTION] = isset($data[CreateAdjustStockRequestInterface::ACTION]) ?
            $data[CreateAdjustStockRequestInterface::ACTION] :
            null;
        return $adjustData;
    }

    /**
     * get products to adjust stock
     *
     * @param array
     * @return array
     */
    protected function getProducts($data){
        $products = [];
        if(isset($data)){
            /** @var \Magestore\InventorySuccess\Api\Data\AdjustStock\ProductInterface $product */
            foreach($data as $product){
                $products[$product->getProductId()] = [
                    'product_sku' => $product->getProductSku(),
                    'product_name' => $product->getProductName(),
                    'old_qty' => $product->getOldQty(),
                    'adjust_qty' => $product->getAdjustQty()
                ];
            }
        }
        return $products;
    }

    /**
     * Get first admin
     *
     * @return string
     */
    protected function getDefaultAdmin()
    {
        return "rest_api";
    }
}