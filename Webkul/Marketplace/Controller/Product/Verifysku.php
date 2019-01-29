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

namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Marketplace Product Verifysku controller.
 * Verify SKU If avialable or not.
 */
class Verifysku extends Action
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResourceModel;

    /**
     * @param \Magento\Framework\App\Action\Context        $context
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResourceModel
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel
    ) {
        $this->_productResourceModel = $productResourceModel;
        parent::__construct($context);
    }

    /**
     * Verify Product SKU availability action.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $skuPrefix = $helper->getSkuPrefix();
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $sku = $this->getRequest()->getParam('sku');
            $sku = $skuPrefix.$sku;
            try {
                $id = $this->_productResourceModel->getIdBySku($sku);
                if ($id) {
                    $avialability = 0;
                } else {
                    $avialability = 1;
                }
                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        'Magento\Framework\Json\Helper\Data'
                    )
                    ->jsonEncode(
                        ['avialability' => $avialability]
                    )
                );
            } catch (\Exception $e) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        'Magento\Framework\Json\Helper\Data'
                    )
                    ->jsonEncode('')
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
