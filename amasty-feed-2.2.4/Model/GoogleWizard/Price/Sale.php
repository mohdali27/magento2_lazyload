<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard\Price;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Sale extends \Amasty\Feed\Model\GoogleWizard\Element
{
    protected $type = 'attribute';

    protected $tag = 'g:sale_price';

    protected $format = 'price';

    protected $value = ExportProduct::PREFIX_PRODUCT_ATTRIBUTE . '|special_price';

    protected $name = 'sale price';

    protected $description = 'Advertised sale price of the item';
}
