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

namespace Webkul\Marketplace\Model\Plugin\Customer;

/**
 * Marketplace Customer Session Plugin.
 */
class Session
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Webkul\Marketplace\Model\ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Webkul\Marketplace\Model\ControllersRepository $controllersRepository
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Webkul\Marketplace\Model\ControllersRepository $controllersRepository
    ) {
        $this->_helper = $helper;
        $this->_urlBuilder = $urlBuilder;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * Insert title and number for concrete document type.
     *
     * @param string $url
     */
    public function beforeAuthenticate(
        \Magento\Customer\Model\Session $session,
        $url = null
    ) {
        if ($this->_helper->getIsSeparatePanel()) {
            $controllerMappedPaths = $this->_helper->getControllerMappedPermissions();
            $currentUrl = $this->_urlBuilder->getCurrentUrl();
            foreach ($controllerMappedPaths as $key => $value) {
                if (strpos($currentUrl, $key) !== false) {
                    $url = $this->_urlBuilder->getUrl("marketplace/account/login");
                }
            }
            if ($url !== null) {
                foreach ($this->controllersRepository->getList() as $coll) {
                    if (strpos($currentUrl, $coll['controller_path']) !== false) {
                        $url = $this->_urlBuilder->getUrl("marketplace/account/login");
                    }
                }
            }
        }
        return [$url];
    }
}
