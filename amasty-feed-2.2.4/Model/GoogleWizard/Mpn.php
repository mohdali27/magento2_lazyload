<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

class Mpn extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:mpn';

    protected $modify = 'html_escape';

    protected $name = 'mpn';

    protected $description = 'Manufacturer Part Number (MPN) of the item';

    protected $limit = 70;
}
