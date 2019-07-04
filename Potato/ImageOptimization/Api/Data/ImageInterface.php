<?php

namespace Potato\ImageOptimization\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * @api
 */
interface ImageInterface extends CustomAttributesDataInterface
{
    const ID = 'id';
    const PATH = 'path';
    const STATUS = 'status';
    const FILE_TIME = 'time';
    const RESULT = 'result';
    const ERROR_TYPE = 'error_type';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getTime();

    /**
     * @param int $time
     * @return $this
     */
    public function setTime($time);

    /**
     * @return string
     */
    public function getResult();

    /**
     * @param string $result
     * @return $this
     */
    public function setResult($result);

    /**
     * @return null|string
     */
    public function getErrorType();

    /**
     * @param null|string $errorType
     * @return $this
     */
    public function setErrorType($errorType);
}
