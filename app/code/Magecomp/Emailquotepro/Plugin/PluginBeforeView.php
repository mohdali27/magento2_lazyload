<?php

namespace Magecomp\Emailquotepro\Plugin;

use Magecomp\Emailquotepro\Helper\Data as EmailquoteHelper;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\ObjectManagerInterface;

class PluginBeforeView
{
    protected $object_manager;
    protected $emailquoteHelper;
    protected $_backendUrl;

    public function __construct(
        ObjectManagerInterface $om,
        EmailquoteHelper $emailquoteHelper,
        UrlInterface $backendUrl
    )
    {
        $this->object_manager = $om;
        $this->emailquoteHelper = $emailquoteHelper;
        $this->_backendUrl = $backendUrl;
    }

    public function afterGetButtonList(
        Context $subject,
        $buttonList
    )
    {
        if ($this->emailquoteHelper->IsAdminActive()) {
            if ($subject->getRequest()->getFullActionName() == 'sales_order_create_index') {
                $productImageUrl = $this->_backendUrl->getUrl('emailquotepro/emailquote/create');
                $buttonList->add(
                    'custom_button',
                    [
                        'label' => __('Email Quote'),
                        'onclick' => "setLocation('" . $productImageUrl . "')",
                        'class' => 'ship primary'
                    ]
                );
            }
        }
        return $buttonList;
    }
}