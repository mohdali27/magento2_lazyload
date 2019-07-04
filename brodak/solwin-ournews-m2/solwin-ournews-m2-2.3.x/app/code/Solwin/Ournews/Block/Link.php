<?php
/**
 * Solwin Infotech
 * Solwin Ournews Extension
 *
 * @category   Solwin
 * @package    Solwin_Ournews
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
?>
<?php

namespace Solwin\Ournews\Block;

class Link extends \Magento\Framework\View\Element\Html\Link
{
    protected $_template = 'Solwin_Ournews::link.phtml';

    public function getHref() {
        return $this->getUrl('ournews');
    }

    public function getLabel() {
        return __('Our news');
    }

}