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
use Magento\Ui\Model\Export\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Class GridToCsv
 */
class GridToCsv extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var ConvertToCsv
     */
    protected $convertToCsv;

    /**
     * @var FileFactory
     */
    protected $httpFile;

    /**
     * @param Context $context
     * @param ConvertToCsv $convertToCsv
     * @param FileFactory $httpFile
     */
    public function __construct(
        Context $context,
        ConvertToCsv $convertToCsv,
        FileFactory $httpFile
    ) {
        parent::__construct($context);
        $this->convertToCsv = $convertToCsv;
        $this->httpFile = $httpFile;
    }

    /**
     * Export UI List data to CSV
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        return $this->httpFile->create(
            'export.csv',
            $this->convertToCsv->getCsvFile(),
            'var'
        );
    }
}
