<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Feed;

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amasty\Feed\Model\Feed\Downloader
     */
    private $feedDownloader;

    /**
     * @var \Amasty\Feed\Model\FeedRepository
     */
    private $feedRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Feed\Model\FeedRepository $feedRepository,
        \Amasty\Feed\Model\Feed\Downloader $feedDownloader
    ) {
        $this->feedDownloader = $feedDownloader;
        $this->feedRepository = $feedRepository;

        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $feedId = $this->getRequest()->getParam('id');
        $fileName = $this->getRequest()->getParam('file');

        try {
            $feedModel = $this->feedRepository->getById($feedId);
        } catch (\Exception $exception) {
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        if ($fileName) {
            $this->feedDownloader->setFilename($fileName);
        }

        if ($feedModel->getIsTemplate() != 1) {
            return $this->feedDownloader->getResponse($feedModel);
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
