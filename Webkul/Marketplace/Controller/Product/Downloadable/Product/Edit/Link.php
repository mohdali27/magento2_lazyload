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

class Link extends \Webkul\Marketplace\Controller\Product\Edit
{
    /**
     * Seller Downloadable Product link action.
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                $linkId = $this->getRequest()->getParam('id', 0);
                $productLink = $this->_objectManager->create(
                    'Magento\Downloadable\Model\Link'
                )->load($linkId);
                $mageProductId = $productLink->getProductId();
                $rightseller = $helper->isRightSeller($mageProductId);
                if (!$rightseller) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/product/productlist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $linkTypeUrl = DownloadableHelper::LINK_TYPE_URL;
                $linkTypeFile = DownloadableHelper::LINK_TYPE_FILE;
                $linkUrl = '';
                $linkType = '';
                $type = $this->getRequest()->getParam('type', 0);
                if ($type == 'link') {
                    if ($productLink->getLinkType() == $linkTypeUrl) {
                        $linkType = $linkTypeUrl;
                        $linkUrl = $productLink->getLinkUrl();
                    } elseif ($productLink->getLinkType() == $linkTypeFile) {
                        $linkType = $linkTypeFile;
                        $linkUrl = $this->_objectManager->get(
                            'Magento\Downloadable\Helper\File'
                        )->getFilePath(
                            $this->_objectManager->get(
                                'Magento\Downloadable\Model\Link'
                            )->getBasePath(),
                            $productLink->getLinkFile()
                        );
                    }
                } elseif ($type == 'sample') {
                    if ($productLink->getSampleType() == $linkTypeUrl) {
                        $linkUrl = $productLink->getSampleUrl();
                        $linkType = $linkTypeUrl;
                    } elseif ($productLink->getSampleType() == $linkTypeFile) {
                        $linkUrl = $this->_objectManager->get(
                            'Magento\Downloadable\Helper\File'
                        )->getFilePath(
                            $this->_objectManager->get(
                                'Magento\Downloadable\Model\Link'
                            )->getBaseSamplePath(),
                            $productLink->getSampleFile()
                        );
                        $linkType = $linkTypeFile;
                    }
                }
                $downloadableHelper = $this->_objectManager->get(
                    'Magento\Downloadable\Helper\Download'
                );
                $downloadableHelper->setResource($linkUrl, $linkType);
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
                        $downloadableHelper->getContentType()
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
                $this->messageManager->addError(
                    __('Something went wrong while getting the requested content.')
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
