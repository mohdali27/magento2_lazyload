<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\RegistryContainer;
use Amasty\Feed\Model\Export\Product as ExportProduct;

class Identifierexists extends Element
{
    protected $type = RegistryContainer::TYPE_CUSTOM_FIELD;

    protected $tag = 'g:identifier_exists';

    protected $format = 'as_is';

    protected $value = 'TRUE';

    protected $template = '<:tag>:value</:tag>' . PHP_EOL;
}
