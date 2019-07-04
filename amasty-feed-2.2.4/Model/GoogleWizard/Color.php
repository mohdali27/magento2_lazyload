<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Color extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:color';

    protected $format = 'html_escape';

    protected $name = 'color';

    protected $description = 'Color of the item';

    protected $limit = 100;
}
