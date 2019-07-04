<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

class Element
{
    protected $type = '';

    protected $value = '';

    protected $modify = '';

    protected $optional = 'yes';

    protected $code;

    protected $tag;

    protected $limit = '';

    protected $format = 'as_is';

    protected $required = false;

    protected $name;

    protected $description;

    protected $template = '<:tag>{attribute=":value" format=":format" parent=":parent" optional=":optional" modify=":modify"}</:tag>' . PHP_EOL;

    protected $feed;

    protected $direcotryData;

    public function __construct(
        \Magento\Directory\Helper\Data $directoryData,
        \Amasty\Feed\Model\Feed $feed
    ) {
        $this->feed = $feed;
        $this->direcotryData = $directoryData;
    }

    /**
     * Get tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Get modify
     *
     * @return string
     */
    public function getModify()
    {
        return $this->modify;
    }

    /**
     * Get limit
     *
     * @return string
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Get optional
     *
     * @return string
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * Get required
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get feed
     *
     * @return object
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Init element code
     *
     * @param string $code
     * 
     * @return object
     */
    public function init($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get tag values
     *
     * @return array
     */
    protected function getEvaluateData()
    {
        return [
            ":tag"      => $this->getTag(),
            ":value"    => $this->getValue(),
            ":format"   => $this->getFormat(),
            ":optional" => $this->getOptional(),
            ":modify"   => $this->getModify(),
            ":parent"   => 'no'
        ];
    }

    /**
     *  Values insert into tag
     *
     * @param array $config
     *
     * @return string
     */
    public function evaluate($config = [])
    {
        $elementWithValue = null;
        $this->reloadData($config);

        $value = $this->getValue();
        $type = $this->getType();

        if ($value && $type) {
            $elementWithValue = strtr($this->template, $this->getEvaluateData());
        }

        return $elementWithValue;
    }

    /**
     *  Reload data
     *
     * @return void
     */
    public function reloadData($config)
    {
        if (isset($config['type'])) {
            $this->type = $config['type'];
        }

        if (isset($config[$this->type])) {
            $this->value = $config[$this->type];
        }
    }
}
