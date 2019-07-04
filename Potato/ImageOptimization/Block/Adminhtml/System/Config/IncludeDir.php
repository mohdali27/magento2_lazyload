<?php
namespace Potato\ImageOptimization\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class IncludeDir
 */
class IncludeDir extends AbstractFieldArray
{
    const INCLUDE_DIR_CONFIG_DEFAULT_VALUE = [
        'pub_static_dir' => ['folder' => 'pub/static'],
        'pub_media_dir' => ['folder' => 'pub/media'],
    ];

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('folder', [
            'label' => __('Folder Name'),
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Overwrite default method for compatibility with 2.0.x - 2.x versions
     * @return array|null
     */
    public function getArrayRows()
    {
        if (!$this->getElement()->getValue()) {
            $this->getElement()->setValue(self::INCLUDE_DIR_CONFIG_DEFAULT_VALUE);
        }
        return parent::getArrayRows();
    }
}