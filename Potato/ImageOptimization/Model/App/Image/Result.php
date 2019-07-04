<?php
namespace Potato\ImageOptimization\Model\App\Image;

class Result
{
    protected $optimized = null;
    protected $original = null;
    protected $result = null;
    protected $status = null;
    protected $alternative = null;

    /**
     * Result constructor.
     * @param \stdClass $image
     */
    public function __construct(\stdClass $image)
    {
        $this->optimized = $image->optimized;
        $this->original = $image->original;
        $this->result = $image->result;
        $this->status = $image->status;
        $this->alternative = $image->alternative;
    }

    /**
     * @return bool
     */
    public function isOptimized()
    {
        return $this->status == 0 ? true: false;
    }

    /**
     * @return string
     */
    public function getOptimizedUrl()
    {
        return $this->optimized;
    }

    /**
     * Return url for pub/static image place
     * @return string
     */
    public function getOriginalUrl()
    {
        return $this->original;
    }

    /**
     * Return url for real image place (if exist)
     * @return string|null
     */
    public function getAlternativeUrl()
    {
        return $this->alternative;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }
}
