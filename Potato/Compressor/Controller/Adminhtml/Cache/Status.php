<?php

namespace Potato\Compressor\Controller\Adminhtml\Cache;

use Magento\Framework\Controller\ResultFactory;
use Potato\Compressor\Controller\Adminhtml\Cache;
use Magento\Backend\App\Action;
use Potato\Compressor\Block\Adminhtml\System\Config\Cache\StatusFactory;

/**
 * Class Status
 */
class Status extends Cache
{
    /** @var StatusFactory */
    protected $statusFactory;

    /**
     * @param Action\Context $context
     * @param StatusFactory $statusFactory
     */
    public function __construct(
        Action\Context $context,
        StatusFactory $statusFactory
    ) {
        parent::__construct($context);
        $this->statusFactory = $statusFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Potato\Compressor\Block\Adminhtml\System\Config\Cache\Status $block */
        $block = $this->statusFactory->create();
        $html = $block->renderHtml();
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(['html' => $html]);
        return $result;
    }
}