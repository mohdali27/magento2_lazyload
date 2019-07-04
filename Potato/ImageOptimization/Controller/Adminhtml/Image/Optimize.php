<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Image;

use Potato\ImageOptimization\Controller\Adminhtml\Image;

/**
 * Class Restore
 */
class Optimize extends Image
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $this->messageManager->addErrorMessage(__('The image no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $image = $this->imageRepository->get($id);
            $this->imageManager->optimizeImage($image);
            $this->messageManager->addSuccessMessage(__('The image has been optimized. Check result'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
