<?php
namespace Magecomp\Emailquotepro\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const GENRAL_ENABLE = 'emailquote/general/enable';
    const ADMINSETTINGS_ENABLE = 'emailquote/adminsettings/enable';
    const GENRAL_HEADING = 'emailquote/general/heading';
    const PDF_ENABLE = 'emailquote/pdfconfig/enable';
    const PDF_FOOTER_TEXT = 'emailquote/pdfconfig/pdffootertext';

    public function __construct( Context $context )
    {
        parent::__construct($context);
    }

    public function IsActive()
    {
        return $this->scopeConfig->getValue(self::GENRAL_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function IsAdminActive()
    {
        return $this->scopeConfig->getValue(self::ADMINSETTINGS_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function getTitle()
    {
        return $this->scopeConfig->getValue(self::GENRAL_HEADING, ScopeInterface::SCOPE_STORE);
    }

    public function isPDFEnable()
    {
        return $this->scopeConfig->getValue(self::PDF_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function getPDFFooterText()
    {
        return $this->scopeConfig->getValue(self::PDF_FOOTER_TEXT, ScopeInterface::SCOPE_STORE);
    }

    public function generateRandomString()
    {
        try {
            $randomString = substr(str_shuffle("0123456789"), 0, 3);
            return $randomString;
        } catch (\Exception $e) {
        }
    }

    public function getConfig( $config_path )
    {
        return $this->scopeConfig->getValue($config_path, ScopeInterface::SCOPE_STORE);
    }
}