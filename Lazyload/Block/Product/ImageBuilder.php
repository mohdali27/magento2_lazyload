<?php
namespace Webapp\Lazyload\Block\Product;

use Magento\Catalog\Helper\ImageFactory as HelperFactory;

class ImageBuilder extends \Magento\Catalog\Block\Product\ImageBuilder
{
    /**
     * @var \Webapp\Lazyload\Helper\Data
     */
    protected $_helperCustom;

    /**
     * @param HelperFactory $helperFactory
     * @param \Magento\Catalog\Block\Product\ImageFactory $imageFactory
     * @param \Webapp\Lazyload\Helper\Data $_helperCustom
     */
    public function __construct(
        HelperFactory $helperFactory,
        \Magento\Catalog\Block\Product\ImageFactory $imageFactory,
        \Webapp\Lazyload\Helper\Data $_helperCustom
    ) {
        $this->_helperCustom = $_helperCustom;
        parent::__construct($helperFactory, $imageFactory);
    }


    /**
     * Create image block
     *
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function create()
    {
        /** Check if module is enabled */
        if (!$this->_helperCustom->isEnabled()) {
            return parent::create();
        }

        $helper = $this->helperFactory->create()
            ->init($this->product, $this->imageId);

        $template = 'Webapp_Lazyload::product/image.phtml';

        $imagesize = $helper->getResizedImageInfo();

        $data = [
            'data' => [
                'template' => $template,
                'image_url' => $helper->getUrl(),
                'width' => $helper->getWidth(),
                'height' => $helper->getHeight(),
                'label' => $helper->getLabel(),
                'ratio' =>  $this->getRatio($helper),
                'custom_attributes' => $this->getCustomAttributes(),
                'resized_image_width' => !empty($imagesize[0]) ? $imagesize[0] : $helper->getWidth(),
                'resized_image_height' => !empty($imagesize[1]) ? $imagesize[1] : $helper->getHeight(),
            ],
        ];

        return $this->imageFactory->create($data);
    }
}