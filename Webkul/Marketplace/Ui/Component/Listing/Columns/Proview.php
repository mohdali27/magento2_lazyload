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

namespace Webkul\Marketplace\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Proview extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->objectManager = $objectManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $adminSession = $this->objectManager->create('Magento\Security\Model\AdminSessionsManager');
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['mageproduct_id'])) {
                    $url = $this->getWebsiteUrl($item['mageproduct_id']);
                    $item[$fieldName] = "<a href='".$url.'marketplace/catalog/view/id/'.$item['mageproduct_id'].'/?SID='.$adminSession->getCurrentSession()->getSessionId()."' target='blank' title='".__('View Product')."'>".__('View').'</a>';
                }
            }
        }

        return $dataSource;
    }

    /**
     * get website url by product id
     * @param  int $productId
     * @return string
     */
    public function getWebsiteUrl($productId)
    {
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $storeManager =  $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $productWebsites = $product->getWebsiteIds();
        $websites = $storeManager->getWebsites();
        $url = '';
        foreach ($websites as $website) {
            if (isset($productWebsites[0]) && $productWebsites[0] == $website->getId()) {
                foreach ($website->getStores() as $store) {
                    $storeObj = $storeManager->getStore($store);
                    $url = $storeObj->getBaseUrl();
                    break;
                }
            }
            if ($url !== '') {
                break;
            }
        }
        return $url;
    }
}
