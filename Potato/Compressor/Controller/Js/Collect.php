<?php
namespace Potato\Compressor\Controller\Js;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Potato\Compressor\Model\RequireJsManager;
use Potato\Compressor\Model\Optimisation\Processor\Js as JsProcessor;
use Potato\Compressor\Model\PageCachePurger;

class Collect extends Action
{
    /** @var  JsonFactory */
    protected $jsonFactory;

    /** @var  RequireJsManager */
    protected $requireJsManager;

    /** @var  JsProcessor */
    protected $jsProcessor;

    /** @var  PageCachePurger */
    protected $pageCachePurger;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        RequireJsManager $requireJsManager,
        JsProcessor $jsProcessor,
        PageCachePurger $pageCachePurger
    ) {
        parent::__construct($context);

        $this->jsonFactory = $jsonFactory;
        $this->requireJsManager = $requireJsManager;
        $this->jsProcessor = $jsProcessor;
        $this->pageCachePurger = $pageCachePurger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $key = $this->_request->getParam('key', null);
        $tags = $this->_request->getParam('tags', null);
        $list = $this->_request->getParam('list', null);
        $baseUrl = $this->_request->getParam('base', null);
        if (null === $key || null === $list || null === $baseUrl || null === $tags) {
            return $this->jsonFactory->create([
                'result' => false
            ]);
        }
        $currentList = $this->requireJsManager->loadUrlList($key);
        if (null === $currentList || count($currentList) === 0) {
            array_unshift($list, $baseUrl); // add base url as first element of list
        } else {
            $list = array_merge($currentList, $list);//push new list to the current list
        }
        $result = $this->requireJsManager->saveUrlList($list, $key);

        //remove cache file
        $filePath = $this->jsProcessor->getRequireJsResultFilePath($key);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        $this->pageCachePurger->purgeByTags($tags);

        return $this->jsonFactory->create([
            'result' => $result
        ]);
    }
}
