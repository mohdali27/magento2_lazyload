<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Availability extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:availability';

    protected $format = 'as_is';

    protected $modify = "replace:1^In Stock|replace:0^Out of Stock";

    protected $value = ExportProduct::PREFIX_INVENTORY_ATTRIBUTE . '|is_in_stock';

    protected $name = 'availability';

    protected $description = 'Availability status of the item';

    protected $required = true;
}
