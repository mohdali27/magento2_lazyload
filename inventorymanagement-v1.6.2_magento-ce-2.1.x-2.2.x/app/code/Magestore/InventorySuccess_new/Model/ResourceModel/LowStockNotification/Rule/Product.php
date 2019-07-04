<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule;

/**
 * Resource Model Supplier
 */
class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /** @var \Magento\Framework\App\ResourceConnection  */
    protected $_resource;

    /**
     * @var int
     */
    protected $batchCount;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magestore\InventorySuccess\Model\LowStockNotification\RuleManagement
     */
    protected $_ruleManagement;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magestore\InventorySuccess\Model\LowStockNotification\RuleManagement $ruleManagement,
        $batchCount = 1000,
        $connectionName = null
    ) {
        $this->_resource = $context->getResources();
        $this->_connection = $this->_resource->getConnection();
        $this->batchCount = $batchCount;
        $this->_messageManager = $messageManager;
        $this->_ruleManagement = $ruleManagement;
        parent::__construct($context, $connectionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('os_lowstock_notification_rule_product', 'rule_product_id');
    }

    /**
     * @param \Magestore\InventorySuccess\Model\LowStockNotification\Rule $ruleModel
     */
    public function applyRule($ruleModel = null)
    {
        if ($ruleModel) {
            $productIds = $this->_ruleManagement->getListProductIdsInRule($ruleModel);
            $this->deleteProductInRule($ruleModel);
            $this->insertProductInRule($ruleModel, $productIds);
            $this->_messageManager->addSuccess(__('Rule is applied.'));
        }
    }

    /**
     * @param $ruleModel
     */
    public function deleteProductInRule($ruleModel)
    {
        $query = $this->_connection->deleteFromSelect(
            $this->_connection
                ->select()
                ->from($this->_resource->getTableName('os_lowstock_notification_rule_product'), 'product_id')
                ->distinct()
                ->where('rule_id = ?', $ruleModel->getId()),
            $this->_resource->getTableName('os_lowstock_notification_rule_product')
        );
        $this->_connection->query($query);
    }

    /**
     * @param $ruleModel
     * @param $productIds
     */
    public function insertProductInRule($ruleModel, $productIds)
    {
        $rows = [];
        $ruleId = $ruleModel->getId();
        foreach ($productIds as $productId) {
            $rows[] = [
                'rule_id' => $ruleId,
                'product_id' => $productId
            ];
            if (count($rows) == $this->batchCount) {
                $this->_connection->insertMultiple($this->_resource->getTableName('os_lowstock_notification_rule_product'), $rows);
                $rows = [];
            }
        }
        if (!empty($rows)) {
            $this->_connection->insertMultiple($this->_resource->getTableName('os_lowstock_notification_rule_product'), $rows);
        }
        $this->_connection->update(
            $this->_resource->getTableName('os_lowstock_notification_rule'),
            ['apply' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::APPLIED],
            ['rule_id = ?' => $ruleId]
        );
    }
}