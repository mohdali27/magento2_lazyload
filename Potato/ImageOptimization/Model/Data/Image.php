<?php

namespace Potato\ImageOptimization\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Potato\ImageOptimization\Api\Data\ImageInterface;

/**
 * Class Image
 */
class Image extends AbstractExtensibleObject implements ImageInterface
{
    /**
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_get(self::PATH);
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->setData(self::PATH, $path);
        return $this;
    }
    
    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @param string $status
     * @return mixed
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->_get(self::FILE_TIME);
    }

    /**
     * @param int $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->setData(self::FILE_TIME, $time);
        return $this;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->_get(self::RESULT);
    }

    /**
     * @param string $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->setData(self::RESULT, $result);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getErrorType()
    {
        return $this->_get(self::ERROR_TYPE);
    }

    /**
     * @param null|string $errorType
     * @return $this
     */
    public function setErrorType($errorType)
    {
        $this->setData(self::ERROR_TYPE, $errorType);
        return $this;
    }
    
    /**
     * @api
     * @return array
     */
    public function toArray()
    {
        return $this->__toArray();
    }
}