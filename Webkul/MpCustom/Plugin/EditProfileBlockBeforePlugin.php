<?php
/**
 * Webkul MpCustom plugins.
 * @category  Webkul
 * @package   Webkul_MpCustom
 * @author    Webkul
 * @copyright Copyright (c) 2010-2019 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpCustom\Plugin;

class EditProfileBlockBeforePlugin
{
    //to set template of edit profile page
    public function beforeToHtml(\Webkul\Marketplace\Block\Account\Editprofile $subject)
    {
        $subject->setTemplate('Webkul_MpCustom::editprofile.phtml');
    }
}
