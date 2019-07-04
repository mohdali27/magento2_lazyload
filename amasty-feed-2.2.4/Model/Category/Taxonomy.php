<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Category;

use Amasty\Feed\Model\ResourceModel\Category\Taxonomy as ResourceTaxonomy;

class Taxonomy extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceTaxonomy::class);
    }
}
