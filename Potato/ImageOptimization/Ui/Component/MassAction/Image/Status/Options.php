<?php

namespace Potato\ImageOptimization\Ui\Component\MassAction\Image\Status;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;

/**
 * Class Options
 */
class Options implements JsonSerializable
{
    /** @var  array */
    protected $options;

    /** @var array  */
    protected $data;

    /** @var UrlInterface  */
    protected $urlBuilder;

    /** @var  string */
    protected $urlPath;

    /** @var  string */
    protected $paramName;

    /** @var array  */
    protected $additionalData = [];

    /** @var StatusSource  */
    protected $statusSource;

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     * @param StatusSource $statusSource
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StatusSource $statusSource,
        array $data = []
    ) {
        $this->statusSource = $statusSource;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->options === null) {
            $options = $this->statusSource->toOptionArray();
            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type' => 'status_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                ];
                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['value']]
                    );
                }
                $this->options[$optionCode['value']] = array_merge_recursive(
                    $this->options[$optionCode['value']],
                    $this->additionalData
                );
            }
            $this->options = array_values($this->options);
        }
        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
