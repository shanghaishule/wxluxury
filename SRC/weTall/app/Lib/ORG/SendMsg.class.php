<?php 
class SendMsg 
{ 
	var $sms_url;
	var $uid;
	var $sms_password;
	var $smstype;
	
	function sms($sms_url,$uid,$sms_password,$smstype = 'json'){
		$this->sms_url = $sms_url; 
		$this->uid = $uid; 
		$this->sms_password = $sms_password;
		$this->smstype = $smstype;
	}
	function sendsms($phone,$content){
		//$str = mb_convert_encoding($content, "GBK", "UTF-8");
		//$sdata="method=".$this->method."&isLongSms=".$this->is_long."&username=".$this->sms_account."&password=".$this->sms_password."&smstype=".$this->smstype."&mobile=".$phone."&content=".$str;
		$sdata="uid=".$this->uid."&pas=".$this->sms_password."&mob=".$phone."&type=".$this->smstype."&con=".urlencode($content);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->sms_url);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
		curl_setopt($ch, CURLOPT_SSLVERSION , 3);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$sdata);
		$res = curl_exec( $ch );
		curl_close( $ch );

		
		$arr = json_decode($res);
		
		return $arr.msg;
	}
}
?> 