<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Mui\Export;

use Magento\Framework\App\Action\Context;
use Magento\Ui\Model\Export\ConvertToXml;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Class GridToXml
 */
class GridToXml extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var ConvertToXml
     */
    protected $convertToXml;

    /**
     * @var FileFactory
     */
    protected $httpFile;

    /**
     * @param Context $context
     * @param ConvertToXml $convertToXml
     * @param FileFactory $httpFile
     */
    public function __construct(
        Context $context,
        ConvertToXml $convertToXml,
        FileFactory $httpFile
    ) {
        parent::__construct($context);
        $this->convertToXml = $convertToXml;
        $this->httpFile = $httpFile;
    }

    /**
     * Export Ui list data to XML
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        return $this->httpFile->create(
            'export.xml',
            $this->convertToXml->getXmlFile(),
            'var'
        );
    }
}
