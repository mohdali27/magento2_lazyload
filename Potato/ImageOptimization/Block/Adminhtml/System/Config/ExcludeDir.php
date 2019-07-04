<?php
namespace Potato\ImageOptimization\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class ExcludeDir
 */
class ExcludeDir extends AbstractFieldArray
{
    const EXCLUDE_DIR_CONFIG_DEFAULT_VALUE = [
        'original_images_dir' => ['folder' => 'po_image_optimization_original_images'],
        'temp_images_dir' => ['folder' => 'po_image_optimization_temp_images'],
        'captcha_dir' => ['folder' => 'captcha'],
        'import_dir' => ['folder' => 'import'],
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
            $this->getElement()->setValue(self::EXCLUDE_DIR_CONFIG_DEFAULT_VALUE);
        }
        return parent::getArrayRows();
    }
}