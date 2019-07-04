<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard\Image;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Additional extends \Amasty\Feed\Model\GoogleWizard\Element
{
    protected $type = 'images';

    protected $tag = 'g:additional_image_link';

    protected $name = 'additional image link';

    protected $description = 'Additional URLs of images of the item';

    protected $required = false;

    protected $limit = 2000;

    public function setImageIdx($idx)
    {
        $this->value = 'image_' . $idx;

        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getModify()
    {
        return $this->modify . ':' . $this->value;
    }

    protected function getEvaluateData()
    {
        $value = strtolower($this->getValue());
        $value = ExportProduct::PREFIX_GALLERY_ATTRIBUTE . '|' . $value;
        $this->setValue($value);

        return parent::getEvaluateData();
    }
}
