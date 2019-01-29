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

namespace Webkul\Marketplace\Block\Product\Helper\Form\Gallery;

use Magento\Catalog\Model\Product;

class Content extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_mediaConfig;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $_fileSizeService;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoderInterface;

    /**
     * Core registry.
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context     $context
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\File\Size                $fileSize
     * @param \Magento\Framework\Json\EncoderInterface    $jsonEncoderInterface
     * @param \Magento\Framework\Registry                 $coreRegistry
     * @param Product                                     $product
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\Json\EncoderInterface $jsonEncoderInterface,
        \Magento\Framework\Registry $coreRegistry,
        Product $product,
        array $data = []
    ) {
        $this->_product = $product;
        $this->_mediaConfig = $mediaConfig;
        $this->_fileSizeService = $fileSize;
        $this->_jsonEncoderInterface = $jsonEncoderInterface;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @return \Magento\Framework\File\Size
     */
    public function getFileSizeService()
    {
        return $this->_fileSizeService;
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
     * Get product image data.
     *
     * @return array
     */
    public function getProductImagesJson()
    {
        $productColl = $this->getProduct();
        $mediaGalleryImages = $productColl->getMediaGalleryImages();
        $productImages = [];
        if (count($mediaGalleryImages) > 0) {
            foreach ($mediaGalleryImages as &$mediaGalleryImage) {
                $mediaGalleryImage['url'] = $this->_mediaConfig->getMediaUrl(
                    $mediaGalleryImage['file']
                );
                array_push($productImages, $mediaGalleryImage->getData());
            }

            return $this->_jsonEncoderInterface->encode($productImages);
        }

        return '[]';
    }

    public function getProductImageTypes()
    {
        $productImageTypes = [];
        $productColl = $this->getProduct();
        foreach ($this->getProductMediaAttributes() as $attribute) {
            $productImageTypes[$attribute->getAttributeCode()] = [
                'code' => $attribute->getAttributeCode(),
                'value' => $productColl[$attribute->getAttributeCode()],
                'label' => $attribute->getFrontend()->getLabel(),
                'name' => 'product['.$attribute->getAttributeCode().']',
            ];
        }

        return $productImageTypes;
    }

    /**
     * @return array
     */
    public function getProductMediaAttributes()
    {
        return $this->_product->getMediaAttributes();
    }
}
