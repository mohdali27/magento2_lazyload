<?php
namespace Mconnect\Customflatrate\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Customflatrate extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'mconnectcustomflatrate';

    protected $_isFixed = true;
    protected $_rateResultFactory;

    protected $_rateMethodFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
		\Mconnect\Customflatrate\Helper\McsHelper $mcsHelper,	
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
		$this->mcsHelper = $mcsHelper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
	$items = $request->getAllItems();
	$perOrderRate = array();
    $shippingPrice = 0;
	if ($request->getAllItems()) {
               $result = $this->_rateResultFactory->create();
           /* start sagar */
		$shippingRateGeneral = $this->getConfigData('default_shipping_cost');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$shippingPriceFinal = 0;
		$isChidrenQty = false;
		$chidrenTotal = 0 ;
		$lastProductType = "";
		foreach($items as $item) {
			$productObject = $objectManager->create('Magento\Catalog\Model\Product');
			$product      = $productObject->load($item->getProductId());
			$productQty   =  $item->getQty();
			$type_id = $product->getTypeId();
			if(($item->getIsVirtual() || $type_id == 'virtual' || $type_id == 'configurable' || $type_id == 'downloadable'  || $type_id == 'grouped' || $type_id == 'bundle')){
					$isChidrenQty = true;
					$chidrenTotal = $productQty;
					$this->_logger->addDebug('chidren '.$chidrenTotal);
					$lastProductType = $product->getTypeId();
					continue;
			}
			
			$is_perproduct_rate = $product->getShippingRate();
			$this->_logger->addDebug('per product type '.$is_perproduct_rate." ".$product->getTypeId());
			if($product->getTypeId() == "simple" && $is_perproduct_rate == ""){
				$this->_logger->addDebug('parent product '.$lastProductType);
				switch($lastProductType){
					case "grouped" :
						$parentIds = $objectManager->create('Magento\GroupedProduct\Model\Product\Type\Grouped')->getParentIdsByChild((int)$product->getId());
						break; 
					case "configurable" :
						$parentIds = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild((int)$product->getId());					break;
					case "bundle" :
						$parentIds = $objectManager->create('Magento\Bundle\Model\Product\Type')->getParentIdsByChild((int)$product->getId());						  							break;
					 default:
						break;
				}
				if(isset($parentIds)){
					$this->_logger->addDebug('is parents  '.count($parentIds));
					foreach($parentIds as $parentIdtmp){
						$parent = $productObject->load($parentIdtmp);
						$this->_logger->addDebug(' parents rate  '.$parent->getShippingRate());
						if(empty($parent->getShippingRate()))
							$is_perproduct_rate = 0;
						else
							$is_perproduct_rate = $parent->getShippingRate();
						if($is_perproduct_rate != ""){ break; }
					}
				}
			}
 
			if(($is_perproduct_rate == '') && $this->getConfigData('shipping_default_value_enable')){
						$this->_logger->addDebug(' global rate  '.$shippingRateGeneral);
						$is_perproduct_rate = $shippingRateGeneral;
		        } 

			if($this->getConfigData('multiply_qty')){
				if($isChidrenQty){
					$shippingPriceFinal += ($chidrenTotal * $is_perproduct_rate);
					$isChidrenQty = false;
					$chidrenTotal = 0;
				}else{
					$shippingPriceFinal += ($productQty * $is_perproduct_rate);	
				}
			}else{
			       $shippingPriceFinal += ($is_perproduct_rate);
			} 
			$perOrderRate[] = $is_perproduct_rate;
			$lastProductType = "";
		}	
		$shippingPrice += $shippingPriceFinal;
	   /* end sagar */
	} 
	else {
            $shippingPrice = false;
        }

	if($this->getConfigData('type') == 'o'){
		 $min_max = ($this->getConfigData('max_min')) ? $this->getConfigData('max_min') : "max";
		 if($min_max == "max")
			$shippingPrice = max($perOrderRate);
		 else
			$shippingPrice = min($perOrderRate);
	}

        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

	$grandTotal = $request->getBaseSubtotalInclTax();
	$free_shipping_over_total = (float)$this->getConfigData('free_shipping_over_total');
	if($this->getConfigData('allow_free_shipping') && $free_shipping_over_total < $grandTotal){
				$shippingPrice = 0.00;
	}
	
        if ($shippingPrice !== false && $this->mcsHelper->checkLicenceKeyActivation() ) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier("mconnectcustomflatrate");
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod("mconnectflatrate");
            $method->setMethodTitle($this->getConfigData('name'));

            /* if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';
            } */

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }

        return $result;
    }
    public function getAllowedMethods()
    {
        return ["mconnectflatrate" => $this->getConfigData('name')];
    }
}
