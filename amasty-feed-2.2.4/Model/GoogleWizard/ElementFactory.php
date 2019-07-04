<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

class ElementFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Amasty\\Feed\\Model\\GoogleWizard\\Element')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Amasty\Feed\Model\GoogleWizard\Element
     */
    public function create(array $data = [])
    {
        $instanceName = $this->_instanceName;
        if (isset($data['elementType'])) {
            $instanceName = $this->prepareInstanceName($data['elementType']);
        }

        return $this->_objectManager->create($instanceName, $data);
    }

    protected function prepareInstanceName($instanceName)
    {
        $partsBaseInstanceName = explode('\\', $this->_instanceName);

        array_pop($partsBaseInstanceName);

        if (strstr($instanceName, '_')) {
            $partsInstanceName = explode('_', $instanceName);
            array_walk($partsInstanceName, function (&$value) {
                $value = ucfirst($value);
            });
            $instanceName = implode('\\', $partsInstanceName);
        }

        $retInstanceName = implode('\\', $partsBaseInstanceName);
        $retInstanceName .= '\\' . ucfirst($instanceName);

        return $retInstanceName;
    }
}
