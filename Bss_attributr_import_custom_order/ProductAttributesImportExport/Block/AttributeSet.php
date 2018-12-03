<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *  
 * @category   BSS
 * @package    Bss_ProductAttributesImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttributesImportExport\Block;

use Magento\Framework\View\Element\Template;

class AttributeSet extends Template
{
    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     */
    protected $attributeSetArr;
    protected $scopeConfig;

    /**
     * AttributeSet constructor.
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetArr
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetArr,
        array $data = []
    ) {
        $this->attributeSetArr = $attributeSetArr;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * @return array|null
     */
    public function getAllAttributeSet()
    {
        return $this->attributeSetArr->toOptionArray();
    }
}
