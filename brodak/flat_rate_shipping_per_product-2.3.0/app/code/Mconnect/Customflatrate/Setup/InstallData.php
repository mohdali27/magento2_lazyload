<?php

namespace Mconnect\Customflatrate\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
	$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
	$eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'shipping_rate',
            [
                'type' => 'decimal',
                'backend' => '',
                'frontend' => '',
                'label' => 'Flat Rate',
                'input' => 'price',
                'class' => '',
                'source' => '',
				'group' => 'M-Connect Custom Shipping',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );
    }
}
