<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Description extends Element
{
    protected $type = 'attribute';

    protected $tag = 'description';

    protected $limit = 500;

    protected $modify = 'html_escape';

    protected $value = ExportProduct::PREFIX_PRODUCT_ATTRIBUTE . '|description';

    protected $required = true;

    protected $name = 'description';

    protected $description = 'Description of the item';

    public function getModify()
    {
        $modify = $this->modify;
        if ($this->limit) {
            $modify .= '|length:' . $this->limit;
        }

        return $modify;
    }
}
