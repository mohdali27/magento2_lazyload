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

namespace Webkul\Marketplace\Block\Order;

/*
 * Webkul Marketplace Order View Block
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Store\Model\ScopeInterface;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var AddressRenderer
     */
    protected $addressRenderer;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_links = [];

    /**
     * @var Purchased
     */
    protected $_purchasedLinks;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $_purchasedFactory;

    /**
     * @var CollectionFactory
     */
    protected $_itemsFactory;

    /**
     * @var \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
     */
    protected $defaultRenderer;

    /**
     * @param Order                                             $order
     * @param Customer                                          $customer
     * @param \Magento\Framework\ObjectManagerInterface         $objectManager
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Magento\Framework\Registry                       $coreRegistry
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param AddressRenderer                                   $addressRenderer
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
     * @param CollectionFactory                                 $itemsFactory
     * @param array                                             $data
     */
    public function __construct(
        Order $order,
        Customer $customer,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Element\Template\Context $context,
        AddressRenderer $addressRenderer,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $defaultRenderer,
        CollectionFactory $itemsFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->addressRenderer = $addressRenderer;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_purchasedFactory = $purchasedFactory;
        $this->defaultRenderer = $defaultRenderer;
        $this->_itemsFactory = $itemsFactory;
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('View Order Detail'));
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getSellerOrderInfo($orderId = '')
    {
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $this->getCustomerId()]
        );

        return $collection;
    }

    public function getOrderCreditmemo($creditmemoIds = '')
    {
        $collection = $this->_objectManager->create(
            'Magento\Sales\Model\Order\Creditmemo'
        )->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $creditmemoIds]
        );

        return $collection;
    }

    public function getCreditmemoItemsCollection($creditmemoId)
    {
        $collection = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo\Item')
        ->getCollection()
        ->addFieldToFilter(
            'parent_id',
            ['eq' => $creditmemoId]
        );

        return $collection;
    }

    public function getOrderInvoice($invoiceId = '')
    {
        $collection = $this->_objectManager->create(
            'Magento\Sales\Model\Order\Invoice'
        )->load($invoiceId);

        return $collection;
    }

    public function getSellerOrdersList($orderId, $proId, $itemId)
    {
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $this->getCustomerId()]
        )
        ->addFieldToFilter(
            'mageproduct_id',
            ['eq' => $proId]
        )
        ->addFieldToFilter(
            'order_item_id',
            ['eq' => $itemId]
        )
        ->setOrder('order_id', 'DESC');

        return $collection;
    }

    public function getAdminPayStatus($orderId)
    {
        $adminPayStatus = 0;
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $this->getCustomerId()]
        );
        foreach ($collection as $saleproduct) {
            $adminPayStatus = $saleproduct->getAdminPayStatus();
        }

        return $adminPayStatus;
    }

    public function getQtyToRefundCollection($orderId)
    {
        $qtyToRefundCollection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $this->getCustomerId()]
        )
        ->addFieldToFilter(
            'magequantity',
            ['neq' => 0]
        );

        return count($qtyToRefundCollection);
    }

    /**
     * @return Purchased
     */
    public function getDownloadableLinks($itemId)
    {
        $this->_purchasedLinks = $this->_purchasedFactory->create()->load(
            $itemId,
            'order_item_id'
        );
        $purchasedItems = $this->_itemsFactory->create()->addFieldToFilter(
            'order_item_id',
            $itemId
        );
        $this->_purchasedLinks->setPurchasedItems($purchasedItems);

        return $this->_purchasedLinks;
    }

    /**
     * @return string
     */
    public function getLinksTitle($itemId)
    {
        return $this->getDownloadableLinks(
            $itemId
        )->getLinkSectionTitle() ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    /**
     * Returns string with formatted address.
     *
     * @param Address $address
     *
     * @return null|string
     */
    public function getFormattedAddress(Address $address)
    {
        return $this->addressRenderer->format($address, 'html');
    }

    public function getLinks()
    {
        $this->checkLinks();

        return $this->_links;
    }

    /**
     * Retrieve current order model instance.
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    /**
     * Retrieve current invoice model instance.
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Retrieve current shipment model instance.
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Retrieve current creditmemo model instance.
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    private function checkLinks()
    {
        $order = $this->getOrder();
        $orderId = $order->getId();
        $shipmentId = '';
        $invoiceId = '';
        $creditmemoId = '';
        $tracking = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Orders'
        )->getOrderinfo($orderId);
        if (count($tracking)) {
            $shipmentId = $tracking->getShipmentId();
            $invoiceId = $tracking->getInvoiceId();
            $creditmemoId = $tracking->getCreditmemoId();
        }
        $this->_links['order'] = [
            'name' => 'order',
            'label' => __('Items Ordered'),
            'url' => $this->_urlBuilder->getUrl(
                'marketplace/order/view',
                [
                    'order_id' => $orderId,
                    '_secure' => $this->getRequest()->isSecure()
                ]
            ),
        ];
        if (!$order->hasInvoices()) {
            unset($this->_links['invoice']);
        } else {
            if ($invoiceId) {
                $this->_links['invoice'] = [
                    'name' => 'invoice',
                    'label' => __('Invoices'),
                    'url' => $this->_urlBuilder->getUrl(
                        'marketplace/order_invoice/view',
                        [
                            'order_id' => $orderId,
                            'invoice_id' => $invoiceId,
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    ),
                ];
            }
        }
        if (!$order->hasShipments()) {
            unset($this->_links['shipment']);
        } else {
            if ($shipmentId) {
                $this->_links['shipment'] = [
                    'name' => 'shipment',
                    'label' => __('Shipments'),
                    'url' => $this->_urlBuilder->getUrl(
                        'marketplace/order_shipment/view',
                        [
                            'order_id' => $orderId,
                            'shipment_id' => $shipmentId,
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    ),
                ];
            }
        }

        if (!$order->hasCreditmemos()) {
            unset($this->_links['creditmemo']);
        } else {
            if ($creditmemoId) {
                $this->_links['creditmemo'] = [
                    'name' => 'creditmemo',
                    'label' => __('Refunds'),
                    'url' => $this->_urlBuilder->getUrl(
                        'marketplace/order_creditmemo/viewlist',
                        [
                            'order_id' => $orderId,
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    ),
                ];
            }
        }
    }

    /**
     * @param mixed $item
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isShipmentSeparately($item = null)
    {
        if ($item) {
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['shipment_type'])
                        && $options['shipment_type'] == 1);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['shipment_type'])
                        && $options['shipment_type'] == 1);
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $item
     *
     * @return mixed|null
     */
    public function getSelectionAttributes($item)
    {
        $options = $item->getProductOptions();
        if (isset($options['bundle_selection_attributes'])) {
            return $this->_objectManager->get(
                'Webkul\Marketplace\Helper\Orders'
            )->getProductOptions(
                $options['bundle_selection_attributes']
            );
        }

        return;
    }

    /**
     * @param mixed $item
     *
     * @return string
     */
    public function getValueHtml($item)
    {
        if ($attributes = $this->getSelectionAttributes($item)) {
            return sprintf('%d', $attributes['qty']).' x '.$this->escapeHtml($item->getName());
        } else {
            return $this->escapeHtml($item->getName());
        }
    }

    /**
     * @param mixed $item
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isChildCalculated($item = null)
    {
        if ($item) {
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['product_calculations'])
                        && $options['product_calculations'] == 0);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['product_calculations'])
                        && $options['product_calculations'] == 0);
                }
            }
        }

        return false;
    }

    /**
     * Return order item's additional information block
     *
     * @return AbstractBlock
     * @codeCoverageIgnore
     */
    public function getOrderItemAdditionalInfoBlock()
    {
        return $this->getLayout()->getBlock('seller.orderitem.info');
    }

    public function getOrderedPricebyorder($currencyRate, $basePrice)
    {
        if (!$currencyRate) {
            $currencyRate = 1;
        }
        return $basePrice * $currencyRate;
    }

    /**
     * @param mixed $item
     * @return mixed|null
     */
    public function getSelectionAttribute($item)
    {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return $this->_objectManager->get(
                'Webkul\Marketplace\Helper\Orders'
            )->getProductOptions(
                $options['bundle_selection_attributes']
            );
        }
        return null;
    }

    /**
     * Return order can ship status
     *
     * @return bool
     */
    public function isOrderCanShip($order)
    {
        if ($order->canShip()) {
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $order->getId()]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $this->getCustomerId()]
            );
            $flag = 0;
            foreach ($collection as $key => $value) {
                $productId = $value->getMageproductId();
                try {
                    $product = $this->_objectManager->create(
                        'Magento\Catalog\Api\ProductRepositoryInterface'
                    )->getById($productId);
                    if ($product->getTypeId() != 'virtual') {
                        $flag = 1;
                    }
                } catch (\Exception $e) {
                    $flag = 0;
                }
            }
            if ($flag) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $optionValue
     * @return array
     */
    public function getFormatedOptionValue($optionValue)
    {
        return $this->defaultRenderer->getFormatedOptionValue($optionValue);
    }
    /**
     * Get All Carriers
     *
     * @return array
     */
    public function getCarriers()
    {
        $carriers = [];
        $carrierInstances = $this->_getCarriersInstances();
        $carriers['custom'] = __('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }

    /**
     * @return array
     */
    protected function _getCarriersInstances()
    {
        $shippingConfig = $this->_objectManager->create('Magento\Shipping\Model\Config');
        return $shippingConfig->getAllCarriers($this->getShipment()->getStoreId());
    }

    /**
     * @param string $code
     * @return \Magento\Framework\Phrase|string|bool
     */
    public function getCarrierTitle($code)
    {
        $carrierFactory = $this->_objectManager->create(
            'Magento\Shipping\Model\CarrierFactory'
        );
        $carrier = $carrierFactory->create($code);
        if ($carrier) {
            return $carrier->getConfigData('title');
        } else {
            return __('Custom Value');
        }
        return false;
    }

    public function trackingAddUrl($orderId = null, $shipmentId = null)
    {
        return $this->_urlBuilder->getUrl(
            'marketplace/order_shipment_tracking/add',
            [
                'order_id' => $orderId,
                'shipment_id' => $shipmentId,
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }

    public function trackingDeleteUrl($orderId = null, $shipmentId = null, $id = null)
    {
        return $this->_urlBuilder->getUrl(
            'marketplace/order_shipment_tracking/delete',
            [
                'order_id' => $orderId,
                'shipment_id' => $shipmentId,
                'id' => $id,
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }

    /**
     * @return array
     */
    public function getItemOptions($item)
    {
        $result = [];
        $productOptions = $item->getProductOptions();
        if ($productOptions) {
            if (isset($productOptions['options'])) {
                $result = array_merge($result, $productOptions['options']);
            }
            if (isset($productOptions['additional_options'])) {
                $result = array_merge($result, $productOptions['additional_options']);
            }
            if (isset($productOptions['attributes_info'])) {
                $result = array_merge($result, $productOptions['attributes_info']);
            }
        }
        return $result;
    }
}
