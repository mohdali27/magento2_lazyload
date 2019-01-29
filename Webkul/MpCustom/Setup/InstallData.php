<?php
/**
 * Webkul MpCustom Data Setup.
 * @category  Webkul
 * @package   Webkul_MpCustom
 * @author    Webkul
 * @copyright Copyright (c) 2010-2019 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpCustom\Setup;

include 'app/bootstrap.php';

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\App\Bootstrap;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $bootstrap = Bootstrap::create(BP, $_SERVER);
        $objectManager = $bootstrap->getObjectManager();
        $state = $objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('global');
        $data = $objectManager->create('Magento\Directory\Model\Country')->getCollection()->getData();
        $taxRateIds = array();

        //create tax rate for every country
        foreach ($data as $country) {
          $taxRate = $objectManager->create('Magento\Tax\Model\Calculation\Rate');
          $taxRate->setCode('wk_custom_'.$country['country_id']);
          $taxRate->setTaxCountryId($country['country_id']);
          $taxRate->setTaxPostcode('*');
          $taxRate->setRate(0);
          $taxRate->setZipIsRange(0);
          $taxRate->save();
          array_push($taxRateIds,$taxRate->getId());
          $taxRate->unsetData();
        }

        //create a product tax class
        $taxClass = $objectManager->create('Magento\Tax\Model\ClassModel');
        $taxClass->setClassName('Custom VAT');
        $taxClass->setClassType('PRODUCT');
        $taxClass->save();

        //create a tax rule
        $taxRule = $objectManager->create('Magento\Tax\Model\Calculation\Rule');
        $taxRule->setCode('custom_VAT');
        $taxRule->setPriority(0);
        $taxRule->setProductTaxClassIds(array($taxClass->getId()));
        $taxRule->setTaxRateIds($taxRateIds);
        $taxRule->setCustomerTaxClassIds(array(3));
        $taxRule->save();
    }
}
