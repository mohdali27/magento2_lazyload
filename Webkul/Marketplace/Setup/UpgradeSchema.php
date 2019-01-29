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
namespace Webkul\Marketplace\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /**
         * Update tables 'marketplace_saleperpartner'
         */
        $setup->getConnection()->changeColumn(
            $setup->getTable('marketplace_saleperpartner'),
            'commission_rate',
            'commission_rate',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'comment' => 'Commission Rate'
            ]
        );

        /**
         * Update tables 'marketplace_saleslist'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'is_shipping',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Is Shipping Applied'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'is_coupon',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Is Coupon Applied'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'is_paid',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Is seller paid for current row'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'commission_rate',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Commission Rate applied at the time of order placed'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'currency_rate',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '1',
                'comment' => 'Ordered currency rate'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'applied_coupon_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Applied coupon amount at the time of order placed'
            ]
        );

        /**
         * Update tables 'marketplace_saleslist'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'is_withdrawal_requested',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Is Withdrawal Requested'
            ]
        );
        /**
         * Update tables 'marketplace_orders'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_orders'),
            'tax_to_seller',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Tax to seller account flag'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_orders'),
            'total_tax',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Tax'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_orders'),
            'coupon_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Coupon Amount'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_orders'),
            'refunded_coupon_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Refunded Coupon Amount'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_orders'),
            'refunded_shipping_charges',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Refunded Shipping Amount'
            ]
        );
        /**
         * Add notification column for orders
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_orders'),
            'seller_pending_notification',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Order Notification flag for sellers'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_orders'),
            'order_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '32',
                'nullable' => false,
                'default' => 'pending',
                'comment' => 'Order Status'
            ]
        );

        /**
         * Add notification column for orders
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_datafeedback'),
            'seller_pending_notification',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Notification flag for review'
            ]
        );

        $this->addForeignKeys($setup);
        $this->dropForeignKeys($setup);

        /**
         * Update tables 'marketplace_product' to add notification columns
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_product'),
            'seller_pending_notification',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Product Notification flag for sellers'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_product'),
            'admin_pending_notification',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Product Notification flag for admin'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_product'),
            'is_approved',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Is product approve by admin for the first time'
            ]
        );

        /**
         * Update table 'marketplace_sellertransaction' to add notification column
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_sellertransaction'),
            'seller_pending_notification',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Notification flag for sellers'
            ]
        );

        /**
         * Update table 'marketplace_userdata' to add notification column
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_userdata'),
            'admin_notification',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Notification flag for admin'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_userdata'),
            'privacy_policy',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'privacy_policy'
            ]
        );

        /**
         * Update table 'marketplace_datafeedback' to add notification column
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_datafeedback'),
            'admin_notification',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Notification flag for admin'
            ]
        );
        /*
         * Create table 'marketplace_controller_list'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_controller_list'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'module_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Webkul Module Name'
            )
            ->addColumn(
                'controller_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Controller Path'
            )
            ->addColumn(
                'label',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Controller Label'
            )
            ->addColumn(
                'is_child',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => false, 'nullable' => false, 'default' => '0'],
                'Is controller have any child Option'
            )
            ->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Status'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Update Time'
            )
            ->setComment('Marketplace Controller List Table');
        $setup->getConnection()->createTable($table);

        /*
         * Create table 'marketplace_order_pendingemails'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_order_pendingemails'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Seller ID'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order ID'
            )
            ->addColumn(
                'myvar1',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'myvar1'
            )
            ->addColumn(
                'myvar2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'myvar2'
            )
            ->addColumn(
                'myvar3',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'myvar3'
            )
            ->addColumn(
                'myvar4',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'myvar4'
            )
            ->addColumn(
                'myvar5',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'myvar5'
            )
            ->addColumn(
                'myvar6',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'myvar6'
            )
            ->addColumn(
                'myvar8',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'myvar8'
            )
            ->addColumn(
                'myvar9',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'myvar9'
            )
            ->addColumn(
                'isNotVirtual',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'isNotVirtual'
            )
            ->addColumn(
                'sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'sender_name'
            )
            ->addColumn(
                'sender_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'sender_email'
            )
            ->addColumn(
                'receiver_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'receiver_name'
            )
            ->addColumn(
                'receiver_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'receiver_email'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'status'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Update Time'
            )
            ->setComment('Marketplace Order Pending Email Table');
        $setup->getConnection()->createTable($table);

        /**
         * Update tables 'sales_order'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'order_approval_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'order_approval_status'
            ]
        );

        /**
         * Update tables 'sales_order_grid'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_grid'),
            'order_approval_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'order_approval_status'
            ]
        );

        /**
         * Update tables 'marketplace_userdata'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_userdata'),
            'allowed_categories',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '',
                'comment' => 'Allowed Categories Ids'
            ]
        );

        /*
         * Create table 'marketplace_notification_list'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_notification_list'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'notification_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Notification Id'
            )
            ->addColumn(
                'notification_row_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Contains Id of product order transaction as per type'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Notification Type'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Update Time'
            )
            ->setComment('Marketplace Notification List Table');
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addForeignKeys(SchemaSetupInterface $setup)
    {
        /**
         * Add foreign keys for Product ID
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'marketplace_product',
                'mageproduct_id',
                'catalog_product_entity',
                'entity_id'
            ),
            $setup->getTable('marketplace_product'),
            'mageproduct_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'marketplace_sellertransaction',
                'seller_id',
                $setup->getTable('customer_entity'),
                'entity_id'
            ),
            $setup->getTable('marketplace_sellertransaction'),
            'seller_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'marketplace_datafeedback',
                'seller_id',
                $setup->getTable('customer_entity'),
                'entity_id'
            ),
            $setup->getTable('marketplace_datafeedback'),
            'seller_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'marketplace_feedbackcount',
                'seller_id',
                $setup->getTable('customer_entity'),
                'entity_id'
            ),
            $setup->getTable('marketplace_feedbackcount'),
            'seller_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        /**
         * Add foreign keys for Order ID
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'marketplace_orders',
                'order_id',
                'sales_order',
                'entity_id'
            ),
            $setup->getTable('marketplace_orders'),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'marketplace_saleslist',
                'order_id',
                'sales_order',
                'entity_id'
            ),
            $setup->getTable('marketplace_saleslist'),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function dropForeignKeys(SchemaSetupInterface $setup)
    {
        /**
         * drop foreign keys for Seller ID
         */
        $setup->getConnection()->dropForeignKey(
            $setup->getTable('marketplace_orders'),
            $setup->getFkName(
                'marketplace_orders',
                'seller_id',
                'customer_entity',
                'entity_id'
            )
        );

        /**
         * drop foreign keys for Seller ID
         */
        $setup->getConnection()->dropForeignKey(
            $setup->getTable('marketplace_saleperpartner'),
            $setup->getFkName(
                'marketplace_saleperpartner',
                'seller_id',
                'customer_entity',
                'entity_id'
            )
        );

        /**
         * drop foreign keys for Seller ID
         */
        $setup->getConnection()->dropForeignKey(
            $setup->getTable('marketplace_userdata'),
            $setup->getFkName(
                'marketplace_userdata',
                'seller_id',
                'customer_entity',
                'entity_id'
            )
        );
    }
}
