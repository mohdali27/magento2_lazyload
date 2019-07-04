<?php
/**
 * Created by PhpStorm.
 * User: duongdiep
 * Date: 25/01/2017
 * Time: 08:58
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Catalog\Model\CategoryFactory;
/**
 * Class Options
 */
class Categoryoptions implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    protected $_categorytFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory, CategoryFactory $categoryFactory)
    {
        $this->_categorytFactory = $categoryFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $subcategory= $this->_categorytFactory->create()->getCollection()
            ->addAttributeToSelect('name')
            ->addIsActiveFilter();
        foreach($subcategory as $key){
            $array = array();
            $array['value'] = $key->getEntityId();
            $array['label'] = $key->getName();
            array_push($options,$array);
        }
        return $options;
    }
}
