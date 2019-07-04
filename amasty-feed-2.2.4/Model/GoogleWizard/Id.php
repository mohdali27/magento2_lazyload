<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Id extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:id';

    protected $limit = 50;

    protected $format = 'html_escape';

    protected $value = ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|sku';

    protected $required = true;

    protected $name = 'id';

    protected $description = 'An identifier of the item';
}
