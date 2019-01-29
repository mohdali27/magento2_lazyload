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
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Webkul Marketplace Product MassDelete controller.
 */
class MassDelete extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var SellerProduct
     */
    protected $_sellerProductCollectionFactory;

    /**
     * @var FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Context           $context
     * @param Session           $customerSession
     * @param Registry          $coreRegistry
     * @param CollectionFactory $productCollectionFactory
     * @param SellerProduct     $sellerProductCollectionFactory
     * @param FormKeyValidator  $formKeyValidator
     * @param HelperData        $helper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $coreRegistry,
        CollectionFactory $productCollectionFactory,
        SellerProduct $sellerProductCollectionFactory,
        FormKeyValidator $formKeyValidator,
        HelperData $helper,
        ProductRepositoryInterface $productRepository = null
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->helper = $helper;
        $this->productRepository = $productRepository
            ?: \Magento\Framework\App\ObjectManager::getInstance()->create(ProductRepositoryInterface::class);
        parent::__construct(
            $context
        );
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Mass delete seller products action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $isPartner = $this->helper->isSeller();
            $assignIds = [];
            if ($isPartner == 1) {
                try {
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/productlist',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $wholedata = $this->getRequest()->getParams();

                    $ids = $this->getRequest()->getParam('product_mass_delete');

                    $sellerId = $this->helper->getCustomerId();
                    $this->_coreRegistry->register('isSecureArea', 1);
                    $deletedIdsArr = [];
                    $sellerProducts = $this->_sellerProductCollectionFactory
                    ->create()
                    ->addFieldToFilter(
                        'mageproduct_id',
                        ['in' => $ids]
                    )->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                    foreach ($sellerProducts as $sellerProduct) {
                        array_push($deletedIdsArr, $sellerProduct['mageproduct_id']);
                        $wholedata['id'] = $sellerProduct['mageproduct_id'];
                        $this->_eventManager->dispatch(
                            'mp_delete_product',
                            [$wholedata]
                        );
                        if ($this->_customerSession->getAssignProductIds()) {
                            $assignIds = $this->_customerSession->getAssignProductIds();
                        }
                        if (!in_array($sellerProduct['mageproduct_id'], $assignIds)) {
                            $sellerProduct->delete();
                        }
                    }

                    foreach ($deletedIdsArr as $id) {
                        try {
                            if (!in_array($id, $assignIds)) {
                                $product = $this->productRepository->getById($id);
                                $this->productRepository->delete($product);
                            }
                        } catch (\Exception $e) {
                            $this->messageManager->addError($e->getMessage());
                        }
                    }
                    $unauthIds = array_diff($ids, $deletedIdsArr);
                    $this->_coreRegistry->unregister('isSecureArea');
                    if (!count($unauthIds)) {
                        // clear cache
                        $this->helper->clearCache();
                        $this->messageManager->addSuccess(
                            __('Products are successfully deleted from your account.')
                        );
                    }

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/productlist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/productlist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/productlist',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
