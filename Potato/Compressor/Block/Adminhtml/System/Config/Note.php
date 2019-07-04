<?php
namespace Potato\Compressor\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Potato\Compressor\Model\SystemNotificationManager;

class Note extends Field
{
    /** @var SystemNotificationManager */
    protected $systemNotificationManager;

    public function __construct(
        Context $context,
        SystemNotificationManager $systemNotificationManager,
        array $data = []
    ) {
        $this->systemNotificationManager = $systemNotificationManager;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_renderHtml();
        if (strlen($html) === 0) {
            return '';
        }
        return '<tr><td colspan="3" style="padding-top:15px;">' . $html . '</td></tr>';
    }

    /**
     * @return string
     */
    protected function _renderHtml()
    {
        $list = $this->systemNotificationManager->getMessageList();
        if (count($list) === 0) {
            return '';
        }
        $html = "";
        foreach ($list as $item) {
            $html .= "<div class=\"message message-error error\">" . $item . "</div>";
        }
        return '<div>' . $html . '</div>';
    }
}
