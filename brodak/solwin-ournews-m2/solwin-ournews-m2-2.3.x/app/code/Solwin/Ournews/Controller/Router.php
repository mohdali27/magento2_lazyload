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

namespace Solwin\Ournews\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url;
use Solwin\Ournews\Model\NewsFactory;

class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $_actionFactory;

    /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var bool
     */
    protected $_dispatched;
    /**
     * @var Solwin\Testimonial\Model\NewsFactory
     */
    protected $_modelNewsFactory;
    /**
     * @var \Solwin\Ournews\Helper\Data $newsHelper
     */
    protected $_newsHelper;
    
    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param ActionFactory $actionFactory   
     * @param ResponseInterface $response        
     * @param ManagerInterface $eventManager    
     * @param NewsFactory $modelNewsFactory
     * @param \Solwin\Ournews\Helper\Data $newsHelper
     * @param StoreManagerInterface  $storeManager    
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        ManagerInterface $eventManager,
        NewsFactory $modelNewsFactory,
        \Solwin\Ournews\Helper\Data $newsHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->_actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_response = $response;
        $this->_newsHelper = $newsHelper;
        $this->_modelNewsFactory = $modelNewsFactory;
        $this->_storeManager = $storeManager;   
    }
    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface
     */
    public function match(RequestInterface $request)
    {
         $_newsHelper = $this->_newsHelper;
        if (!$this->_dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
             
            $origUrlKey = $urlKey;
            /** @var Object $condition */
            $condition = new DataObject(['url_key' => $urlKey,
                'continue' => true]);
            $this->_eventManager->dispatch(
                'solwin_ournews_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
                );
              
            $urlKey = $condition->getUrlKey();
            if ($condition->getRedirectUrl()) {
                $this->_response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->_actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                    );
            }
            if (!$condition->getContinue()) {
                return null;
            }
          
            $route = $_newsHelper->getConfig('newssection/newsgroup/route');
            if ( $urlKey == $route ) {
                $request->setModuleName('ournews')
                ->setControllerName('index')
                ->setActionName('index');
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                $this->_dispatched = true;
                return $this->_actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                    );
            }
            $urlPrefix = $_newsHelper
                    ->getConfig('newssection/newsgroup/url_prefix');
            $urlSuffix = $_newsHelper
                    ->getConfig('newssection/newsgroup/url_suffix');

            $identifiers = explode('/', $urlKey);
            
            $cnt = count($identifiers);
            
            if ($cnt > 1) {
                $pos = strpos($identifiers[1], $urlSuffix);
            } else {
                $pos = false;
            }
           
            // Check News Url Key
            if ((count($identifiers) == 2 && $identifiers[0] == $urlPrefix 
                    && $pos !== false) 
                    || (trim($urlPrefix) == '' && count($identifiers) == 1)) {
                if (count($identifiers) == 2) {
                    $testimonialUrl = str_replace($urlSuffix, '',
                            $identifiers[1]);
                }
                if (trim($urlPrefix) == '' && count($identifiers) == 1) {
                    $testimonialUrl = str_replace($urlSuffix, '',
                            $identifiers[0]);
                }
                
                $news = $this->_modelNewsFactory->create()->getCollection()
                        ->addFieldToFilter('is_active', ['eq' => 1])
                        ->addFieldToFilter('url_key',
                                ['eq' => $testimonialUrl]);
                $newscollection = [];
                $newscollection = $news->getData();
                if ($newscollection && $newscollection[0]['news_id']) {
                    $request->setModuleName('ournews')
                    ->setControllerName('index')
                    ->setActionName('index')
                    ->setParam('id', $newscollection[0]['news_id']);
                    $request->setAlias(
                            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                            $origUrlKey);
                    $request->setDispatched(true);
                    $this->_dispatched = true;
                    return $this->_actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
                }
            }
        }
    }
}