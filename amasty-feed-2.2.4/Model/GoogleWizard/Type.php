<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Type extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:product_type';

    protected $modify = 'html_escape';

    protected $value = ExportProduct::PREFIX_CATEGORY_ATTRIBUTE . '|category';

    protected $name = 'product type';

    protected $description = 'Your category of the item';
}
