<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Size extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:size';

    protected $format = 'html_escape';

    protected $name = 'size';

    protected $description = 'Size of the item';

    protected $limit = 100;
}
