<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Amasty\Feed\Model\FeedFactory as FeedFactory;
use Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedResourceFactory;

class Import
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    protected $_templates = [
        'google', 'bing', 'shopping'
    ];

    public function __construct(
        SampleDataContext $sampleDataContext,
        FeedFactory $feedFactory,
        FeedResourceFactory $feedResourceFactory,
        \Amasty\Base\Model\Serializer $serializer
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->feedFactory = $feedFactory;
        $this->feedResourceFactory = $feedResourceFactory;
        $this->serializer = $serializer;
    }

    public function install()
    {
        foreach ($this->_templates as $template) {
            $fileName = $this->fixtureManager->getFixture('Amasty_Feed::fixtures/' . $template);

            if (!file_exists($fileName)) {
                continue;
            }

            $content = @file_get_contents($fileName);

            $data = @$this->serializer->unserialize($content);

            if (is_array($data)) {

                $feedCollection = $this->feedResourceFactory->create()
                    ->addFieldToFilter('name', $data['name'])
                    ->addFieldToFilter('is_template', 1);

                if ($feedCollection->getSize() > 0) {
                    $items = $feedCollection->getItems();
                    end($items)->delete();
                }

                $feedModel = $this->feedFactory->create();
                $feedModel->setData($data);
                $feedModel->save();
            }
        }
    }

    public function update($templates)
    {
        if (!is_array($templates)) {
            $templates = [$templates];
        }

        $this->_templates = $templates;

        $this->install();
    }
}
