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
namespace Webkul\Marketplace\Plugin\Framework\Search\Adapter\Mysql;

use Magento\Framework\Search\Adapter\Mysql\Mapper;

class Adapter
{
    /**
     * Mapper instance
     *
     * @var \Magento\Framework\Search\Adapter\Mysql\Mapper
     */
    protected $mysqlMapper;

    /**
     * Response Factory
     *
     * @var \Magento\Framework\Search\Adapter\Mysql\ResponseFactory
     */
    protected $mysqlResponseFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder
     */
    private $builder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $_collection;

    /**
     * @param \Magento\Framework\Search\Adapter\Mysql\Mapper $mysqlMapper
     * @param \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $mysqlResponseFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder $builder
     * @param \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\Collection $collection
     */
    public function __construct(
        \Magento\Framework\Search\Adapter\Mysql\Mapper $mysqlMapper,
        \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $mysqlResponseFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder $builder,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\Marketplace\Model\ResourceModel\Product\Collection $collection
    ) {
        $this->mysqlMapper = $mysqlMapper;
        $this->mysqlResponseFactory = $mysqlResponseFactory;
        $this->resourceConnection = $resourceConnection;
        $this->builder = $builder;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->helper = $helper;
        $this->request = $request;
        $this->_collection = $collection;
    }

    public function aroundQuery(
        \Magento\Framework\Search\Adapter\Mysql\Adapter $subject,
        callable $proceed,
        \Magento\Framework\Search\RequestInterface $request
    ) {
        if ($this->request->getFullActionName() == 'marketplace_seller_collection') {
            $marketplaceProduct = $this->_collection->getTable('marketplace_product');
            $sellerId = $this->getProfileDetail()->getSellerId();
            $updatedQuery = $this->mysqlMapper->buildQuery($request);
            $updatedQuery->join(
                ['mpp' => $marketplaceProduct],
                'mpp.mageproduct_id = main_select.entity_id',
                ''
            )->where("mpp.seller_id = '".$sellerId."'");
            $temporaryStorage = $this->temporaryStorageFactory->create();
            $table = $temporaryStorage->storeDocumentsFromSelect($updatedQuery);

            $sellerDocuments = $this->getDocuments($table);
            $sellerAggregations = $this->builder->build(
                $request,
                $table,
                $sellerDocuments
            );
            $response = [
                'documents' => $sellerDocuments,
                'aggregations' => $sellerAggregations,
            ];
            return $this->mysqlResponseFactory->create($response);
        }
        return $proceed($request);
    }

    /**
     * @return array
     */
    public function getProfileDetail($value = '')
    {
        $shopUrl = $this->helper->getCollectionUrl();
        if (!$shopUrl) {
            $shopUrl = $this->request->getParam('shop');
        }
        if ($shopUrl) {
            $data = $this->helper->getSellerCollectionObjByShop($shopUrl);
            foreach ($data as $seller) {
                return $seller;
            }
        }
    }

    /**
     * Executes query and return raw response
     *
     * @param \Magento\Framework\DB\Ddl\Table $table
     * @return array
     * @throws \Zend_Db_Exception
     */
    private function getDocuments(\Magento\Framework\DB\Ddl\Table $table)
    {
        $resourceConnection = $this->getConnection();
        $select = $resourceConnection->select();
        $select->from($table->getName(), ['entity_id', 'score']);
        return $resourceConnection->fetchAssoc($select);
    }

    /**
     * @return false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }
}
