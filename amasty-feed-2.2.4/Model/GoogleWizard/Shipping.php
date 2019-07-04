<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

class Shipping extends Element
{
    protected $type = 'attribute';

    protected $value = 'shipping';

    protected $template = '<g:shipping>
    <g:country>::country</g:country>
    <g:price>0 ::currency</g:price>
</g:shipping>' . PHP_EOL;

    protected function getEvaluateData()
    {
        $data = parent::getEvaluateData();
        $data['::country'] = $this->direcotryData->getDefaultCountry();
        $data['::currency'] = $this->getFeed()->getFormatPriceCurrency();

        return $data;
    }
}
