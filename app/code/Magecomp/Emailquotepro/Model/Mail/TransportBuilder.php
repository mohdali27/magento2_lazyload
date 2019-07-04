<?php

namespace Magecomp\Emailquotepro\Model\Mail;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    public function addAttachment( $pdfString, $enable )
    {
        if ($enable) {
            $this->message->createAttachment(
                $pdfString,
                'application/pdf',
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                'Estimate_Quote.pdf'
            );
        }
        return $this;
    }

    public function clearHeader( $headerName )
    {
        if (isset($this->_headers[$headerName])) {
            unset($this->_headers[$headerName]);
        }
        return $this;
    }
}