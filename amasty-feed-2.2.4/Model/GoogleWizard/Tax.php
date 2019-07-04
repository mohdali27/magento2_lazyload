<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Tax extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:rate';

    protected $format = 'as_is';

    protected $value = ExportProduct::PREFIX_OTHER_ATTRIBUTES . '|tax_percents';

    protected $name = 'tax';

    protected $description = 'The tax rate as a percent of the item price, i.e., a number as a percentage';

    protected $required = true;

    protected $template = '<g:tax>
    <g:country>::country</g:country>
    <:tag>{attribute=":value" format=":format" parent=":parent" optional=":optional" modify=":modify"}</:tag>
    <g:tax_ship>y</g:tax_ship>
</g:tax>' . PHP_EOL;

    protected function getEvaluateData(){
        $data = parent::getEvaluateData();
        $data['::country'] = $this->direcotryData->getDefaultCountry();

        return $data;
    }
}
