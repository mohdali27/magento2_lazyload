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

namespace Webkul\Marketplace\Controller\Transaction;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Transaction DownloadCsv Controller.
 */
class DownloadCsv extends Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session       $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get(
            'Magento\Customer\Model\Url'
        )->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Add product to shopping cart action.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        try {
            if (!($customerId = $this->getCustomerId())) {
                return false;
            }

            $ids = [];
            $orderids = [];

            $trId = '';
            $filterDataTo = '';
            $filterDataFrom = '';
            $from = null;
            $to = null;
            if (isset($params['tr_id'])) {
                $trId = $params['tr_id'] != '' ? $params['tr_id'] : '';
            }
            if (isset($params['from_date'])) {
                $filterDataFrom = $params['from_date'] != '' ? $params['from_date'] : '';
            }
            if (isset($params['to_date'])) {
                $filterDataTo = $params['to_date'] != '' ? $params['to_date'] : '';
            }

            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Sellertransaction'
            )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            );

            if ($filterDataTo) {
                $todate = date_create($filterDataTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if ($filterDataFrom) {
                $fromdate = date_create($filterDataFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            if ($trId) {
                $collection->addFieldToFilter(
                    'transaction_id',
                    $trId
                );
            }

            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $from, 'to' => $to]
            );

            $collection->setOrder(
                'created_at',
                'desc'
            );

            $data = [];
            foreach ($collection as $transactioncoll) {
                $data1 = [];
                $data1['Date'] = $transactioncoll->getCreatedAt();
                $data1['Transaction Id'] = $transactioncoll->getTransactionId();
                if ($transactioncoll->getCustomNote()) {
                    $data1['Comment Message'] = $transactioncoll->getCustomNote();
                } else {
                    $data1['Comment Message'] = __('None');
                }
                $data1['Transaction Amount'] = $transactioncoll->getTransactionAmount();
                $data[] = $data1;
            }

            if (isset($data[0])) {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename=transactionlist.csv');
                header('Pragma: no-cache');
                header('Expires: 0');

                $outstream = fopen('php://output', 'w');
                fputcsv($outstream, array_keys($data[0]));

                foreach ($data as $result) {
                    fputcsv($outstream, $result);
                }

                fclose($outstream);
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/transaction/history',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_objectManager->get(
                'Psr\Log\LoggerInterface'
            )->critical($e);

            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/transaction/history',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
