<?php

namespace Potato\ImageOptimization\Model\App;

use Potato\ImageOptimization\Model\App\Image\Result as ImageResult;
use Potato\ImageOptimization\Logger\Logger;
use Magento\Framework\Webapi\Response;

class ImageOptimization
{
    const API_URL = 'http://app.potatocommerce.com/image_optimization';

    /** @var Logger  */
    protected $logger;

    /**
     * ImageOptimization constructor.
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param string $callback
     * @param array $images
     * @return string|bool
     */
    public function process($callback, $images)
    {
        $data = \Zend_Json::encode(
            [
                'callback' => $callback,
                'images'   => $images
            ]
        );
        $ch = curl_init(self::API_URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ]
        );
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($output === false || false === $httpCode || $httpCode !== Response::HTTP_OK) {
            $this->logger->error(
                __(
                    "No output data is returned from service. (HTTP code: %1, error code: %2, error message: %3)",
                    $httpCode,
                    curl_errno($ch),
                    curl_error($ch)
                )
            );
        }
        return $output;
    }

    /**
     * @param mixed $result
     * @return array
     * @throws \Exception
     */
    public function getOptimizedImages($result)
    {
        if (empty($result)) {
            throw new \Exception('Result data is empty');
        }
        if (!is_string($result)) {
            throw new \Exception('String is required');
        }
        if (!$images = json_decode($result)) {
            throw new \Exception('Invalid JSON data. Error code %1', json_last_error());
        }
        $collection = [];
        foreach ($images as $image) {
            $collection[] = new ImageResult($image);
        }
        return $collection;
    }
}
