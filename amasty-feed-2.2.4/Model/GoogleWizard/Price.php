<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Price extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:price';

    protected $format = 'price';

    protected $value = ExportProduct::PREFIX_PRICE_ATTRIBUTE . '|final_price';

    protected $name = 'price';

    protected $description = 'Price of the item';

    protected $required = true;

    protected $template
        = '<:tag>{attribute=":value" format=":format" parent=":parent" optional=":optional" modify=":modify"}</:tag>'
        . PHP_EOL;
}
