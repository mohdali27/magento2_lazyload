<?php
namespace Potato\ImageOptimization\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class ArraySerialized
 */
class ArraySerialized extends Value
{
    /** @var mixed|null */
    private $serializer = null;

    /**
     * ArraySerialized constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        if (@class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('\Magento\Framework\Serialize\Serializer\Json');
        }
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $value = $this->getValue();
        if (array_key_exists('__empty', $value)) {
            unset($value['__empty']);
        }
        if (!$value) {
            $this->_dataSaveAllowed = false;
            return $this;
        }
        if (null !== $this->serializer) {
            $this->setValue($this->serializer->serialize($value));
        } else {
            $this->setValue(serialize($value));
        }
        return $this;
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    public function afterLoad()
    {
        parent::afterLoad();
        if ($this->getValue()) {
            if (null !== $this->serializer) {
                $value = $this->serializer->unserialize($this->getValue());
            } else {
                $value = unserialize($this->getValue());
            }
            if (is_array($value)) {
                $this->setValue($value);
            }
        }
        return $this;
    }
}