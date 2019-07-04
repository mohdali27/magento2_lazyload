<?php
namespace Potato\Compressor\App;

use Magento\Framework\App;
use Magento\Framework\AppInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Potato\Compressor\Model\Optimisation\Processor\Merger\Image as ImageMerger;
use Magento\Framework\App\Cache\Frontend\Factory as CacheFactory;

class ImageMerge implements AppInterface
{
    /** @var ResponseHttp */
    protected $response;

    /** @var RequestHttp */
    protected $request;

    /** @var CacheFactory */
    protected $cacheFactory;

    /**
     * @param ResponseHttp $response
     * @param RequestHttp  $request
     * @param CacheFactory $cacheFactory
     */
    public function __construct(
        ResponseHttp $response,
        RequestHttp $request,
        CacheFactory $cacheFactory
    ) {
        $this->response = $response;
        $this->request = $request;
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * Run application
     *
     * @return ResponseHttp
     * @throws \LogicException
     */
    public function launch()
    {
        $file = $this->request->getParam('f', null);
        if (null === $file) {
            $this->response->setHttpResponseCode(404);
            return $this->response;
        }
        $key = ImageMerger::CACHE_KEY . '_' . $file;
        $cacheInstance = $this->cacheFactory->create([])->getBackend();

        $content = $cacheInstance->load($key);
        if (!$content) {
            $this->response->setHttpResponseCode(404);
            return $this->response;
        }

        $this->response->clearHeaders();
        $this->response->setHeader('Content-Length', strlen($content));
        $this->response->setHeader('Content-Type', 'application/javascript');
        $metadata = $cacheInstance->getMetadatas($key);
        $this->response->setHeader('Last-Modified', gmdate("D, d M Y H:i:s \G\M\T", $metadata['mtime']));
        $expire = intval($metadata['mtime']) + (3600 * 24 * 30);
        $this->response->setHeader('Expires', gmdate("D, d M Y H:i:s \G\M\T", $expire));

        $this->response->setBody($content);
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        $this->response->setHttpResponseCode(404);
        if ($bootstrap->isDeveloperMode()) {
            $this->response->setHeader('Content-Type', 'text/plain');
            $this->response->setBody($exception->getMessage() . "\n" . $exception->getTraceAsString());
        }
        $this->response->sendResponse();
        return true;
    }
}
