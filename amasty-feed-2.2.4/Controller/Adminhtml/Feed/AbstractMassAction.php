<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Magento\Framework\Exception\LocalizedException;

abstract class AbstractMassAction extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    protected $feedCopier;
    protected $filter;
    protected $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\Feed\Copier $feedCopier,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $logger);

        $this->feedCopier = $feedCopier;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            if ($ids = $this->getRequest()->getParam('selected')) {
                /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $collection */
                $collection  = $this->getCollection()->addFieldToFilter(
                    'entity_id',
                    ['in' => implode(',', $ids)]
                );
                if (!$collection->getSize()) {
                    throw new LocalizedException(__('This feed no longer exists.'));
                }
                $this->massAction($collection);
            } else {
                $collection = $this->getCollection();
                if (!$collection->getSize()) {
                    throw new LocalizedException(__('This feed no longer exists.'));
                }
                $this->massAction($collection);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong. Please review the error log.')
            );
            $this->logger->critical($e);
        }

        $this->_redirect('amfeed/*/index');
    }

    /**
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    private function getCollection()
    {
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()->addFieldToFilter('is_template', ['neq' => 1])
        );

        return $collection;
    }

    abstract protected function massAction($collection);
}
