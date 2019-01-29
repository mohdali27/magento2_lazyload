<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Block\Product\Edit;

use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class NewVideo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\ProductVideo\Helper\Media
     */
    protected $_productVideoMediaHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_mathRandom;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\ProductVideo\Helper\Media $productVideoMediaHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\ProductVideo\Helper\Media $productVideoMediaHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_productVideoMediaHelper = $productVideoMediaHelper;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_mathRandom = $mathRandom;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get html id
     *
     * @return mixed
     */
    public function getHtmlId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->_mathRandom->getUniqueHash('id_'));
        }
        return $this->getData('id');
    }

    /**
     * Get widget options
     *
     * @return string
     */
    public function getVideoEncodedOptions()
    {
        return $this->_jsonEncoder->encode(
            [
                'saveVideoUrl' => $this->getUrl(
                    'marketplace/product_gallery/upload',
                    [
                        '_secure' => $this->getRequest()->isSecure()
                    ]
                ),
                'saveRemoteVideoUrl' => $this->getUrl(
                    'marketplace/product_gallery/retrieveImage',
                    ['_secure' => $this->getRequest()->isSecure()]
                ),
                'htmlId' => $this->getHtmlId(),
                'youTubeApiKey' => $this->_productVideoMediaHelper->getYouTubeApiKey()
            ]
        );
    }

    /**
     * Retrieve product.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @return array
     */
    public function getProductMediaAttributes()
    {
        return $this->getProduct()->getMediaAttributes();
    }

    /**
     * Get note for video url
     *
     * @return \Magento\Framework\Phrase
     */
    public function getNoteVideoUrl()
    {
        $result = __('YouTube and Vimeo supported.');
        if ($this->_productVideoMediaHelper->getYouTubeApiKey() === null) {
            $result = __(
                'Vimeo supported.<br />'
                . 'To add YouTube video, please ask admin to set YouTube API Key first.'
            );
        }
        return $result;
    }
}
