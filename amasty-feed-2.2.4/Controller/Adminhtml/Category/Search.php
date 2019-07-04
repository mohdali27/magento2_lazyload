<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;
use Psr\Log\LoggerInterface;
use Amasty\Feed\Model\RegistryContainer;
use Magento\Framework\Registry;
use Amasty\Feed\Model\ResourceModel\Category\Taxonomy\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class Search extends \Amasty\Feed\Controller\Adminhtml\Category
{
    const LANGUAGE_CODE = 'language_code';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $logger);

        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultCategory = [];

        $category = $this->getRequest()->getParam('category');
        $source = $this->getRequest()->getParam('source');

        if ($source && $category) {
            /** @var \Amasty\Feed\Model\ResourceModel\Category\Taxonomy $categories */
            $categories = $this->collectionFactory->create()
                ->addFieldToFilter(RegistryContainer::TYPE_CATEGORY, ['like' => '%' . $category . '%'])
                ->addFieldToFilter(self::LANGUAGE_CODE, ['eq' => $source])
                ->getData();

            foreach ($categories as $item) {
                $resultCategory[] = $item['category'];
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($resultCategory);

        return $resultJson;
    }
}
