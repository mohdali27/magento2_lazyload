<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

class Gender extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:gender';

    protected $format = 'html_escape';

    protected $name = 'gender';

    protected $description = 'Gender of the item';
}
