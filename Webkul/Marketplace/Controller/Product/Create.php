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
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Product Create Controller Class.
 */
class Create extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @param Context                                     $context
     * @param Session                                     $customerSession
     * @param FormKeyValidator                            $formKeyValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Filesystem                                  $filesystem
     * @param PageFactory                                 $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Filesystem $filesystem,
        PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->_resultPageFactory = $resultPageFactory;
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
        $loginUrl = $this->_objectManager->get(
            'Magento\Customer\Model\Url'
        )->getLoginUrl();

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
     * Seller Product Create page.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                $allowedAttributesetIds = $helper->getAllowedAttributesetIds();
                $allowedProductType = $helper->getAllowedProductType();
                $allowedsets = [];
                $allowedtypes = [];
                if (trim($allowedAttributesetIds)) {
                    $allowedsets = explode(',', $allowedAttributesetIds);
                }
                if (trim($allowedProductType)) {
                    $allowedtypes = explode(',', $allowedProductType);
                }
                if (count($allowedsets) > 1 || count($allowedtypes) > 1) {
                    if (!$this->getRequest()->isPost()) {
                        /** @var \Magento\Framework\View\Result\Page $resultPage */
                        $resultPage = $this->_resultPageFactory->create();
                        if ($helper->getIsSeparatePanel()) {
                            $resultPage->addHandle('marketplace_layout2_product_create');
                        }
                        $resultPage->getConfig()->getTitle()->set(
                            __('Add New Product')
                        );

                        return $resultPage;
                    }
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/create',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $set = $this->getRequest()->getParam('set');
                    $type = $this->getRequest()->getParam('type');
                    if (isset($set) && isset($type)) {
                        if (!in_array($type, $allowedtypes) || !in_array($set, $allowedsets)) {
                            $this->messageManager->addError(
                                'Product Type Or Attribute Set Invalid Or Not Allowed'
                            );

                            return $this->resultRedirectFactory->create()
                            ->setPath(
                                '*/*/create',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                        }
                        $this->_getSession()->setAttributeSet($set);

                        return $this->resultRedirectFactory->create()
                        ->setPath(
                            '*/*/add',
                            [
                                'set' => $set,
                                'type' => $type,
                                '_secure' => $this->getRequest()->isSecure(),
                            ]
                        );
                    } else {
                        $this->messageManager->addError(
                            __('Please select attribute set and product type.')
                        );

                        return $this->resultRedirectFactory->create()
                        ->setPath(
                            '*/*/create',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                } elseif (count($allowedsets) == 0 || count($allowedtypes) == 0) {
                    $this->messageManager->addError(
                        'Please ask admin to configure product settings properly to add products.'
                    );

                    return $this->resultRedirectFactory->create()
                    ->setPath(
                        'marketplace/account/dashboard',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } else {
                    $this->_getSession()->setAttributeSet($allowedsets[0]);

                    return $this->resultRedirectFactory->create()
                    ->setPath(
                        '*/*/add',
                        [
                            'set' => $allowedsets[0],
                            'type' => $allowedtypes[0],
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()
                ->setPath(
                    '*/*/create',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()
            ->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
