<?php


class Mconnect_Config_Config {	
	public function __construct(){
		
	}
	
	public function getRequestUrl() {
		$protocol = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			$protocol = 'https';
		}
	
		$portNo = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		return $protocol . "://" . $this->getRequestHost() . $portNo . $_SERVER['REQUEST_URI'];
	}
	
	public function getEncData(){	
		return '__COMPILER_HALT_OFFSET__';	
	}
	
	
	public function getRequestHost() {
		if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && !empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
			$hostExplode = explode(',', $host);
			$host = trim(end($hostExplode));
		}
		else {
			if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
				$host = $_SERVER['HTTP_HOST'];
			}
			else {
				if (isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
					$host = $_SERVER['SERVER_NAME'];
				}
				else {
					if (isset($_SERVER['SERVER_ADDR']) && !empty($_SERVER['SERVER_ADDR'])) {
						$host = $_SERVER['SERVER_ADDR'];
					}
					else {
						$host = '';
					}
				}
			}
		}	
		
		$host = preg_replace('/:\d+$/', '', $host);
	
		return trim($host);
	}
	
	public static function getCompilerHaltOffset($file) {
		if (defined('__COMPILER_HALT_OFFSET__')) {
			return __COMPILER_HALT_OFFSET__;
		}

		$handle = fopen($file, 'r');
		$buffer = '';
		while (false !== ($char = fgetc($handle))) {
			$buffer .= $char;
			if ($buffer == '__halt_compiler();') {
				return ftell($handle);
			} elseif (strpos('__halt_compiler();', $buffer) !== 0) {
				$buffer = '';
			}
		}

		throw new Exception('__halt_compiler() not found.');
	}
	
	
}