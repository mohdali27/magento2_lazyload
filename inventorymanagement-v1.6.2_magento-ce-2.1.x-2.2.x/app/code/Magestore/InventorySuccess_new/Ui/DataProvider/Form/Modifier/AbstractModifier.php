<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\Component\Form;
use Magento\Framework\UrlInterface;
use Magestore\InventorySuccess\Model\Permission\PermissionManagement;


/**
 * Class AbstractModifier
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class AbstractModifier extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
    implements ModifierInterface
{
    /**
     * Collapsible
     *
     * @var string
     */
    protected $_collapsible = true;

    /**
     * Group Container
     *
     * @var string
     */
    protected $_visible = true;

    /**
     * Group Container
     *
     * @var string
     */
    protected $_opened = true;

    /**
     * sort Sales
     *
     * @var string
     */
    protected $_sortOrder = '1';

    /**
     * Modifier Config
     *
     * @var array
     */
    protected $_modifierConfig = [];

    /**
     * Group Label
     *
     * @var string
     */
    protected $_groupLabel;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * is required
     *
     * @var boolean
     */
    protected $isRequried = true;

    /**
     * is required
     *
     * @var boolean
     */
    protected $visibleImage;

    /** @var  \Magestore\InventorySuccess\Model\Permission\PermissionManagement */
    protected $_permissionManagement;

    const TMPL_INPUT = 'ui/form/element/input';
    const TMPL_TEXTAREA = 'ui/form/element/textarea';
    const TMPL_SELECT = 'ui/form/element/select';
    const TMPL_DATE = 'ui/form/element/date';
    const TMPL_TEXT_LABEL = 'Magestore_InventorySuccess/form/element/text';
    const TMPL_TEXTAREA_LABEL = 'Magestore_InventorySuccess/form/element/textarea';
    const TMPL_SELECT_LABEL = 'Magestore_InventorySuccess/form/element/selectlabel';

    /**
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $_modifierConfig
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        array $modifierConfig = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->_modifierConfig = array_replace_recursive($this->_modifierConfig, $modifierConfig);
    }
    
    /**
     * set visible
     *
     * @param boolean
     * @return
     */
    public function setVisible($visible){
        $this->_visible = $visible;
    }

    /**
     * get visible
     *
     * @param
     * @return
     */
    public function getVisible(){
        return $this->_visible;
    }

    /**
     * get visible
     *
     * @param
     * @return bool
     */
    public function getVisibleImage(){
        if($this->visibleImage == '' || $this->visibleImage == null) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $om->get('Magestore\InventorySuccess\Helper\Data');
            $this->visibleImage = $helper->getShowThumbnailProduct() ? true : false;
        }

        return $this->visibleImage;
    }

    /**
     * set opened
     *
     * @param boolean
     * @return
     */
    public function setOpened($opened){
        $this->_opened = $opened;
    }

    /**
     * get opened
     *
     * @param
     * @return boolean
     */
    public function getOpened(){
        return $this->_opened;
    }

    /**
     * set collapsible
     *
     * @param boolean
     * @return
     */
    public function setCollapsible($collapsible){
        $this->_collapsible = $collapsible;
    }

    /**
     * get collapsible
     *
     * @param
     * @return boolean
     */
    public function getCollapsible(){
            return $this->_collapsible;
    }

    /**
     * set group label
     *
     * @param boolean
     * @return
     */
    public function setGroupLabel($groupLabel){
        $this->_groupLabel = $groupLabel;
    }

    /**
     * get group label
     *
     * @param
     * @return boolean
     */
    public function getGroupLabel(){
            return $this->_groupLabel;
    }

    /**
     * set sort order
     *
     * @param boolean
     * @return
     */
    public function setSortOrder($sortOrder){
        $this->_sortOrder = $sortOrder;
    }

    /**
     * get is required
     *
     * @param
     * @return
     */
    public function getIsRequired(){
        return $this->isRequried;
    }

    /**
     * set is required
     *
     * @param boolean
     * @return
     */
    public function setIsRequired($isRequired){
        $this->isRequried = $isRequired;
    }

    /**
     * get sort order
     *
     * @param
     * @return
     */
    public function getSortOrder(){
        return $this->_sortOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data){
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta){
        return $meta;
    }

    /**
     * get modify tmpl
     *
     * @param
     * @return
     */
    public function getModifyTmpl($type)
    {
        switch ($type){
            case 'input':
                return static::TMPL_INPUT;
                break;
            case 'date':
                return static::TMPL_DATE;
                break;
            case 'textarea':
                return static::TMPL_TEXTAREA;
                break;
            case 'select':
                return static::TMPL_SELECT;
                break;
            default:
                return static::TMPL_INPUT;
        }
    }

}
