<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard\Price;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Effectivedate extends \Amasty\Feed\Model\GoogleWizard\Element
{
    protected $type = 'attribute';

    protected $tag = 'g:sale_price_effective_date';

    protected $format = 'as_is';

    protected $value = ExportProduct::PREFIX_OTHER_ATTRIBUTES . '|sale_price_effective_date';

    protected $name = 'sale price effective date';

    protected $description = 'Date range during which the item is on sale';

    protected $limit = 71;
}
