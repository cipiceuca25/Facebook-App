<?php
class Fancrank_Crypt extends Zend_Crypt
{
	const VECTOR = 'fancrank';
	const DEFAULT_KEY = 'superfan';
	
	public static function encrypt($data, $key=null) {
		if(is_array($data)) {
			$data = Zend_Json::encode($data);	
		}
		
		$key = !empty($key) && is_string($key) ? $key : self::DEFAULT_KEY;
		 
		$options = array(
						'adapter'   => 'mcrypt',
						'key'	=> $key
					);
		
		$filter = new Zend_Filter_Encrypt($options);
		$filter->setVector(self::VECTOR);
		
		return base64_encode(trim($filter->filter($data)));
	}
	
	public static function decrypt($data, $key=null) {
		$key = !empty($key) && is_string($key) ? $key : self::DEFAULT_KEY;
		
		$options = array(
						'adapter'   => 'mcrypt',
						'key'	=> $key
					);
		
		$filter = new Zend_Filter_Decrypt($options);
		$filter->setVector(self::VECTOR);
		
		$decryptText = base64_decode(str_replace(" ","+", $data));
		
		$result = rtrim($filter->filter($decryptText));
		
		try {
			$result = Zend_Json::decode($result, Zend_Json::TYPE_ARRAY);
		}catch (Exception $e) {
		}
		return $result;
	}
	
}
