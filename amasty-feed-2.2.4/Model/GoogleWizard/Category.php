<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\RegistryContainer;
use Amasty\Feed\Model\Export\Product as ExportProduct;

class Category extends Element
{
    protected $type = RegistryContainer::TYPE_CATEGORY;

    protected $tag = 'g:google_product_category';

    protected $modify = 'html_escape|length:150';

    public function setValue($value)
    {
        $this->value = ExportProduct::PREFIX_MAPPED_CATEGORY_ATTRIBUTE . '|' . $value;
    }
}
