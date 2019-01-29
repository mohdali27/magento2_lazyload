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

namespace Webkul\Marketplace\Controller\Product\Downloadable\Product\Edit;

use Magento\Downloadable\Helper\Download as DownloadableHelper;

class Sample extends \Webkul\Marketplace\Controller\Product\Edit
{
    /**
     * Seller Downloadable Product Sample action.
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                $sampleId = $this->getRequest()->getParam('id', 0);
                $productSample = $this->_objectManager->create(
                    'Magento\Downloadable\Model\Sample'
                )->load($sampleId);
                $mageProductId = $productSample->getProductId();
                $helper = $this->_objectManager->get(
                    'Webkul\Marketplace\Helper\Data'
                );
                $rightseller = $helper->isRightSeller($mageProductId);
                if (!$rightseller) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/product/productlist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $sampleTypeUrl = DownloadableHelper::LINK_TYPE_URL;
                $sampleTypeFile = DownloadableHelper::LINK_TYPE_FILE;
                $sampleUrl = '';
                $sampleType = '';
                if ($productSample->getSampleType() == $sampleTypeUrl) {
                    $sampleUrl = $productSample->getSampleUrl();
                    $sampleType = $sampleTypeUrl;
                } elseif ($productSample->getSampleType() == $sampleTypeFile) {
                    $sampleUrl = $this->_objectManager->get(
                        'Magento\Downloadable\Helper\File'
                    )->getFilePath(
                        $this->_objectManager->get(
                            'Magento\Downloadable\Model\Sample'
                        )->getBasePath(),
                        $productSample->getSampleFile()
                    );
                    $sampleType = $sampleTypeFile;
                }
                $downloadableHelper = $this->_objectManager->get(
                    'Magento\Downloadable\Helper\Download'
                );
                $downloadableHelper->setResource($sampleUrl, $sampleType);
                $this->getResponse()
                    ->setHttpResponseCode(200)
                    ->setHeader(
                        'Cache-Control',
                        'must-revalidate, post-check=0, pre-check=0',
                        true
                    )
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader(
                        'Content-type',
                        $downloadableHelper->getContentType(),
                        true
                    );
                if ($downloadableHelper->getFileSize()) {
                    $this->getResponse()->setHeader(
                        'Content-Length',
                        $downloadableHelper->getFileSize()
                    );
                }
                if ($contentDisposition = $downloadableHelper->getContentDisposition()) {
                    $this->getResponse()->setHeader(
                        'Content-Disposition',
                        $contentDisposition.'; filename='.$downloadableHelper->getFilename()
                    );
                }
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();
                $downloadableHelper->output();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
