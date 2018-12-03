<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductAttributesImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttributesImportExport\Plugin;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadSampleFilePlugin extends \Magento\ImportExport\Controller\Adminhtml\Import\Download
{

    const PRODUCT_ATTRIBUTES_SAMPLE_FILE = 'Bss_ProductAttributesImportExport';

    /**
     * @param \Magento\ImportExport\Controller\Adminhtml\Import\Download $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute($subject, callable $proceed)
    {
        $fileName = $this->getRequest()->getParam('filename') . '.csv';
        if ($this->getRequest()->getParam('filename')=='product_attributes') {
            $moduleDir = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                self::PRODUCT_ATTRIBUTES_SAMPLE_FILE
            );
            $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
            $directoryRead = $this->readFactory->create($moduleDir);
            $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

            if (!$directoryRead->isFile($filePath)) {
                $this->messageManager->addError(__('There is no sample file for this entity.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/import');
                return $resultRedirect;
            }

            $fileSize = isset($directoryRead->stat($filePath)['size'])
                ? $directoryRead->stat($filePath)['size'] : null;

            $this->fileFactory->create(
                $fileName,
                null,
                DirectoryList::VAR_DIR,
                'application/octet-stream',
                $fileSize
            );
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setContents($directoryRead->readFile($filePath));
            return $resultRaw;
        } else {
            return $proceed();
        }
    }
}
