<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Brand extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:brand';

    protected $format = 'html_escape';

    protected $value = ExportProduct::PREFIX_PRODUCT_ATTRIBUTE . '|manufacturer';

    protected $name = 'brand';

    protected $description = 'Brand of the item';

    protected $limit = 70;
}
