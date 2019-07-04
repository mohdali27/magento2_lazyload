<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\RegistryContainer;
use Amasty\Feed\Model\Export\Product as ExportProduct;

class Image extends Element
{
    protected $type = RegistryContainer::TYPE_ATTRIBUTE;

    protected $tag = 'g:image_link';

    protected $limit = 2000;

    protected $format = 'as_is';

    protected $value = ExportProduct::PREFIX_IMAGE_ATTRIBUTE . '|thumbnail';

    protected $name = 'image link';

    protected $description = 'URL of an image of the item';

    protected $required = true;
}
