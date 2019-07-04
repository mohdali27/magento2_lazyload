<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Feed;

class Downloader
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $rawResultFactory;

    /**
     * @var string
     */
    private $filename = null;

    public function __construct(
      \Magento\Framework\Controller\Result\RawFactory $rawResultFactory
    ) {
        $this->rawResultFactory = $rawResultFactory;
    }

    /**
     * @param \Amasty\Feed\Api\Data\FeedInterface $feed
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function getResponse(\Amasty\Feed\Api\Data\FeedInterface $feed)
    {
        $rawResult = $this->rawResultFactory->create();
        $output = $feed->getOutput();
        $filename = $this->filename ?: $output['filename'];
        $rawResult->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Content-Length', strlen($output['content']), true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"', true) // не работает для мультибайтовых кодировок
            ->setHeader('Last-Modified', date('r', $output['mtime']), true)
            ->setContents($output['content']);

        return $rawResult;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
}
