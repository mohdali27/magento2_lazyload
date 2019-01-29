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

namespace Webkul\Marketplace\Controller\Product\Downloadable\File;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Marketplace Product Downloadable File Upload controller.
 */
class Upload extends Action implements AccountInterface
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $_fileUploaderFactory;

    /**
     * @param \Magento\Backend\App\Action\Context               $context
     * @param \Magento\MediaStorage\Model\File\UploaderFactory  $fileUploaderFactory
     */
    public function __construct(
        Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        parent::__construct($context);
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }

    /**
     * Marketplace Downloadable Upload file controller action
     *
     * @return json data
     */
    public function execute()
    {
        $helper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                $fileType = $this->getRequest()->getParam('type');
                $destPath = '';
                if ($fileType == 'links') {
                    $destPath = $this->_objectManager->get(
                        'Magento\Downloadable\Model\Link'
                    )->getBaseTmpPath();
                } elseif ($fileType == 'samples') {
                    $destPath = $this->_objectManager->get(
                        'Magento\Downloadable\Model\Sample'
                    )->getBaseTmpPath();
                } elseif ($fileType == 'link_samples') {
                    $destPath = $this->_objectManager->get(
                        'Magento\Downloadable\Model\Link'
                    )->getBaseSampleTmpPath();
                }
                $fileUploader = $this->_fileUploaderFactory->create(
                    ['fileId' => $fileType]
                );
                $resultData = $this->_objectManager->get(
                    'Magento\Downloadable\Helper\File'
                )->uploadFromTmp($destPath, $fileUploader);
                if (!$resultData) {
                    throw new LocalizedException('File can not be uploaded.');
                }
                if (isset($resultData['file'])) {
                    $relativePath = rtrim($destPath, '/') . '/' . ltrim($resultData['file'], '/');
                    $this->_objectManager->get(
                        'Magento\MediaStorage\Helper\File\Storage\Database'
                    )->saveFile($relativePath);
                }
                return $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        'Magento\Framework\Json\Helper\Data'
                    )->jsonEncode($resultData)
                );
            } catch (\Exception $e) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        'Magento\Framework\Json\Helper\Data'
                    )->jsonEncode(
                        [
                            'error' => $e->getMessage(),
                            'errorcode' => $e->getCode()
                        ]
                    )
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
