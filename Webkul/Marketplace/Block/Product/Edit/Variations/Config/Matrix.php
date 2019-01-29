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
/**
 * Marketplace Product variations matrix block.
 */

namespace Webkul\Marketplace\Block\Product\Edit\Variations\Config;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\View\Element\Template\Context;

class Matrix extends \Magento\Framework\View\Element\Template
{
    /*
    * @var \Magento\Catalog\Helper\Image 
    */
    protected $_helperImage;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var Configurable
     */
    protected $_configurableProductType;

    /**
     * @var VariationMatrix
     */
    protected $_configurableProductVariationMatrix;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepositoryInterface;

    /**
     * @var StockRegistryInterface
     */
    protected $_stockRegistryInterface;

    private $_configurableProductMatrix;

    private $_configurableProductAttributes;

    /**
     * @param Context                       context
     * @param \Magento\Catalog\Helper\Image $helperImage
     * @param Configurable                  $configurableProductType
     * @param VariationMatrix               $configurableProductVariationMatrix
     * @param \Magento\Framework\Registry   $registry
     * @param ProductRepositoryInterface    $productRepositoryInterface
     * @param StockRegistryInterface        $stockRegistryInterface
     * @param array                         $data
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Image $helperImage,
        Configurable $configurableProductType,
        VariationMatrix $configurableProductVariationMatrix,
        \Magento\Framework\Registry $registry,
        ProductRepositoryInterface $productRepositoryInterface,
        StockRegistryInterface $stockRegistryInterface,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helperImage = $helperImage;
        $this->_configurableProductType = $configurableProductType;
        $this->_configurableProductVariationMatrix = $configurableProductVariationMatrix;
        $this->_registry = $registry;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_stockRegistryInterface = $stockRegistryInterface;
    }

    public function getSellerProduct()
    {
        return $this->_registry->registry('product');
    }

    /**
     * Retrieve all possible attribute values combinations.
     *
     * @return array
     */
    public function getConfigurableProductVariationMatrix()
    {
        return $this->_configurableProductVariationMatrix
        ->getVariations($this->getConfigurableAttributes());
    }

    /**
     * @param array $initData
     *
     * @return string
     */
    public function getVariationStepsWizard($initData)
    {
        /** @var \Magento\Ui\Block\Component\StepsWizard $variationWizardBlock */
        $variationWizardBlock = $this->getChildBlock('variation-steps-wizard');
        if ($variationWizardBlock) {
            $variationWizardBlock->setInitData($initData);

            return $variationWizardBlock->toHtml();
        }

        return '';
    }

    /**
     * Get Configurable attributes data.
     *
     * @return array
     */
    protected function getConfigurableAttributes()
    {
        if (!$this->hasData('attributes')) {
            $confAttributes = (array) $this->_configurableProductType
            ->getConfigurableAttributesAsArray($this->getSellerProduct());
            $productData = (array) $this->getRequest()->getParam('product');
            if (isset($productData['configurable_attributes_data'])) {
                $configurableAttributeData = $productData['configurable_attributes_data'];
                foreach ($confAttributes as $key => $confAttribute) {
                    if (isset($configurableAttributeData[$key])) {
                        $confAttributes[$key] = array_replace_recursive(
                            $confAttribute,
                            $configurableAttributeData[$key]
                        );
                        $confAttributes[$key]['values'] = array_merge(
                            isset($confAttribute['values']) ? $confAttribute['values'] : [],
                            isset($configurableAttributeData[$key]['values'])
                            ? array_filter($configurableAttributeData[$key]['values'])
                            : []
                        );
                    }
                }
            }
            $this->setAttributes($confAttributes);
        }

        return $this->getAttributes();
    }

    /**
     * get all list of Configurable associated products.
     *
     * @return array
     */
    protected function getConfigurableAssociatedProducts()
    {
        $usedProductAttributes = $this->_configurableProductType
        ->getUsedProductAttributes(
            $this->getSellerProduct()
        );
        $productByUsedAttributes = [];
        foreach ($this->_getConfigurableAssociatedProducts() as $product) {
            $keys = [];
            foreach ($usedProductAttributes as $confAttribute) {
                /* @var $confAttribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                $keys[] = $product->getData($confAttribute->getAttributeCode());
            }
            $productByUsedAttributes[implode('-', $keys)] = $product;
        }

        return $productByUsedAttributes;
    }

    /**
     * @return array
     */
    protected function _getConfigurableAssociatedProducts()
    {
        $product = $this->getSellerProduct();
        $associatedProductIds = $this->getSellerProduct()->getAssociatedProductIds();
        if ($associatedProductIds === null) {
            return $this->_configurableProductType->getUsedProducts($product);
        }
        $products = [];
        foreach ($associatedProductIds as $associatedProductId) {
            try {
                $products[] = $this->_productRepositoryInterface->getById($associatedProductId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
        }

        return $products;
    }

    /**
     * @return array|null
     */
    public function getSellerProductMatrix()
    {
        if ($this->_configurableProductMatrix === null) {
            $this->prepareConfigurableProductVariation();
        }

        return $this->_configurableProductMatrix;
    }

    /**
     * @return array|null
     */
    public function getSellerProductAttributes()
    {
        if ($this->_configurableProductAttributes === null) {
            $this->prepareConfigurableProductVariation();
        }

        return $this->_configurableProductAttributes;
    }

    protected function prepareConfigurableProductVariation()
    {
        $configurableProductVariations = $this->getConfigurableProductVariationMatrix();
        $configurableProductMatrix = [];
        $confAttributes = [];
        if ($configurableProductVariations) {
            $usedProductAttributes = $this->_configurableProductType->getUsedProductAttributes(
                $this->getSellerProduct()
            );
            $productByUsedAttributes = $this->getConfigurableAssociatedProducts();
            foreach ($configurableProductVariations as $configurableProductVariation) {
                $confAttributeValues = [];
                foreach ($usedProductAttributes as $confAttribute) {
                    $confAttributeValues[$confAttribute->getAttributeCode()] =
                    $configurableProductVariation[
                        $confAttribute->getId()
                    ]['value'];
                }
                $key = implode('-', $confAttributeValues);
                if (isset($productByUsedAttributes[$key])) {
                    $product = $productByUsedAttributes[$key];
                    $price = $product->getPrice();
                    $configurableProductVariationOptions = [];
                    foreach ($usedProductAttributes as $confAttribute) {
                        $confAttributeId = $confAttribute->getAttributeId();
                        $confAttributeCode = $confAttribute->getAttributeCode();
                        if (!isset($confAttributes[$confAttributeId])) {
                            $confAttributes[$confAttributeId] = [
                                'code' => $confAttributeCode,
                                'label' => $confAttribute->getStoreLabel(),
                                'id' => $confAttributeId,
                                'position' => $confAttribute->getPosition(),
                                'chosen' => [],
                            ];
                            foreach ($confAttribute->getOptions() as $option) {
                                $optionValue = $option->getValue();
                                if (!empty($optionValue)) {
                                    $confAttributes[$confAttributeId]['options'][
                                        $option->getValue()
                                    ] = [
                                        'attribute_code' => $confAttributeCode,
                                        'attribute_label' => $confAttribute->getStoreLabel(0),
                                        'id' => $option->getValue(),
                                        'label' => $option->getLabel(),
                                        'value' => $option->getValue(),
                                    ];
                                }
                            }
                        }
                        $optionId = $configurableProductVariation[$confAttribute->getId()]['value'];
                        $configurableProductVariationOption = [
                            'attribute_code' => $confAttributeCode,
                            'attribute_label' => $confAttribute->getStoreLabel(0),
                            'id' => $optionId,
                            'label' => $configurableProductVariation[$confAttribute->getId()]['label'],
                            'value' => $optionId,
                        ];
                        $configurableProductVariationOptions[] = $configurableProductVariationOption;
                        $confAttributes[$confAttributeId]['chosen'][$optionId] =
                        $configurableProductVariationOption;
                    }

                    $productQty = $this->_stockRegistryInterface->getStockItem(
                        $product->getId(),
                        $product->getStore()->getWebsiteId()
                    )->getQty();

                    $configurableProductMatrix[] = [
                        'productId' => $product->getId(),
                        'images' => [
                            'preview' => $this->_helperImage->init(
                                $product,
                                'product_thumbnail_image'
                            )->getUrl(),
                        ],
                        'sku' => $product->getSku(),
                        'name' => $product->getName(),
                        'quantity' => $productQty,
                        'price' => $price,
                        'options' => $configurableProductVariationOptions,
                        'weight' => $product->getWeight(),
                        'status' => $product->getStatus(),
                    ];
                }
            }
        }
        $this->_configurableProductMatrix = $configurableProductMatrix;
        $this->_configurableProductAttributes = array_values($confAttributes);
    }
}
