<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Image;

use Magento\Framework\Controller\ResultFactory;
use Potato\ImageOptimization\Controller\Adminhtml\Image;

/**
 * Class Index
 */
class Index extends Image
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Processed Images (Image Optimizer by PotatoCommerce)'));

        return $resultPage;
    }
}