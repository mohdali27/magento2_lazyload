<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

class Gtin extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:gtin';

    protected $format = 'html_escape';

    protected $name = 'gtin';

    protected $description = 'Global Trade Item Number (GTIN) of the item<br/>Please check <a target="_blank" href="https://support.google.com/merchants/answer/6219078?hl=en">here</a> for details on GTIN and MPN';

    protected $limit = 50;
}
