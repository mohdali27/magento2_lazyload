<?php
/**
 * Solwin Infotech
 * Solwin Ournews Extension
 *
 * @category   Solwin
 * @package    Solwin_Ournews
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
?>
<?php

namespace Solwin\Ournews\Block\Adminhtml\News\Helper;

use Magento\Framework\Data\Form\Element\CollectionFactory;

/**
 * @method string getValue()
 */
class Image extends \Magento\Framework\Data\Form\Element\Image
{
    /**
     * News image model
     * 
     * @var \Solwin\Ournews\Model\News\Image
     */
    protected $_imageModel;

    /**
     * constructor
     * 
     * @param \Solwin\Ournews\Model\News\Image $imageModel
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Solwin\Ournews\Model\News\Image $imageModel,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $data
    ) {
        $this->_imageModel = $imageModel;
        parent::__construct($factoryElement, $factoryCollection, $escaper,
                $urlBuilder, $data);
    }

    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->_imageModel->getBaseUrl().$this->getValue();
        }
        return $url;
    }
}