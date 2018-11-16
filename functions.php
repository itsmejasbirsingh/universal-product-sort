<?php
class UPSFeatured
{
	public function isEmpty($val)
	{
		if(isSet($val) && trim($val))
		{
			return false;
		}
		return true;
	}

	public function safeStr($str)
	{
		$str = trim($str);
		if($str){
		$str = str_replace('"','``',$str);
		$str = str_replace("'","`",$str);
		$str = htmlspecialchars($str);
		$str = stripslashes($str);
		}
		
		return $str;
	}
}