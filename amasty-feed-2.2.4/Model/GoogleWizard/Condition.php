<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

class Condition extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:condition';

    protected $format = 'as_is';

    protected $required = true;

    protected $name = 'condition';

    protected $value = 'new';

    protected $description = 'Condition or state of the item (allowed values: new, refubrished, used)';

    protected $template = '<:tag>:value</:tag>' . PHP_EOL;

    public function getValue()
    {
        $value = parent::getValue();

        return strtolower($value);
    }

    protected function getEvaluateData()
    {
        return [
            ":tag"      => $this->getTag(),
            ":value"    => $this->getValue()
        ];
    }
}
