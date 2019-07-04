<?php
namespace Mconnect\Customflatrate\Helper;

class McsHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
	
	protected $scopeConfigObject;
	
	public function __construct(			
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigObject			
			){			
			$this->scopeConfigObject = $scopeConfigObject;			
			} 	 
	
	public function checkLicenceKeyActivation($storeId = null)
    {
        if($this->isProductionUrl($this->getRequestHost())) {
            return true;
        }
		if($this->getLicenceKey() && $this->getSerialKey()){
			
			if( !(($this->simple_encrypt($this->getRequestHost()."Mconnect_Customflatrate",substr($this->getLicenceKey(),1,16)) == $this->getSerialKey()) || ($this->simple_encrypt($this->getWebsiteHost()."Mconnect_Customflatrate",substr($this->getLicenceKey(),1,16)) == $this->getSerialKey())) ){
				
				return false;	
			}else{
				
				return true;
				
			}			
			
		}		
        
    }
	
	public function getLicenceKey(){
		return $this->scopeConfigObject->getValue("carriers/active/licence_key", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore());
	}	
	public function getSerialKey(){
		return $this->scopeConfigObject->getValue("carriers/active/serial_key", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore());
	}
	
	public function simple_encrypt($host, $key)
	{
		$abcd = array("+", "/", "\/", "=");
    return str_replace($abcd , "",trim(base64_encode(@mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $host, MCRYPT_MODE_ECB, @mcrypt_create_iv(@mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))));
	}
	public function isProductionUrl($host) {
        $isRealHost = false;
        if (strpos($host, "localhost") !== false) {
            $isRealHost = true;
        } else if(is_numeric(str_replace(".","",$host))) {
            $isRealHost = true;
        }
        return $isRealHost;
    }
	public function getRequestHost() {
		if (isset($_SERVER["HTTP_X_FORWARDED_HOST"]) && !empty($_SERVER["HTTP_X_FORWARDED_HOST"])) {
			$host = $_SERVER["HTTP_X_FORWARDED_HOST"];
			$hostExplode = explode(",", $host);
			$host = trim(end($hostExplode));
		}
		else {
			if (isset($_SERVER["HTTP_HOST"]) && !empty($_SERVER["HTTP_HOST"])) {
				$host = $_SERVER["HTTP_HOST"];
			}
			else {
				if (isset($_SERVER["SERVER_NAME"]) && !empty($_SERVER["SERVER_NAME"])) {
					$host = $_SERVER["SERVER_NAME"];
				}
				else {
					if (isset($_SERVER["SERVER_ADDR"]) && !empty($_SERVER["SERVER_ADDR"])) {
						$host = $_SERVER["SERVER_ADDR"];
					}
					else {
						$host = "";
					}
				}
			}
		}		
		$host = preg_replace("/:\d+$/", "", $host);	
		
		if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $host, $regs)) {
			return $regs["domain"];
		}
      return $host;
	}
    
    public function getStore() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
        
        $store_id = $storeManager->getStore()->getStoreId();
		return $store_id;
	}
	
}
