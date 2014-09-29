<?php 
class SendMsg 
{ 
	var $sms_url;
	var $uid;
	var $sms_password;
	var $cid;
	var $smstype;
	
	/**
	 * 传入基本参数，参考微米短信接口后台
	 * @param string $sms_url 请求地址
	 * @param string $uid 短信接口申请者id
	 * @param stgring $sms_password 接口密码
	 * @param string $cid 短信模板id，在后台查看需要调用的模板
	 * @param string $smstype 接口返回类型
	 */
	public function __construct($sms_url,$uid,$sms_password,$cid,$smstype = 'json'){
		$this->sms_url = $sms_url; 
		$this->uid = $uid; 
		$this->sms_password = $sms_password;
		$this->cid = $cid;
		$this->smstype = $smstype;
	}
	/**
	 * 找回密码发送验证码
	 * $param string $phone 申请人电话
	 * @param string $p1 验证码
	 */
	public function sendsms($phone,$p1){
		//$str = mb_convert_encoding($content, "GBK", "UTF-8");
		//$sdata="method=".$this->method."&isLongSms=".$this->is_long."&username=".$this->sms_account."&password=".$this->sms_password."&smstype=".$this->smstype."&mobile=".$phone."&content=".$str;
		header("Content-type:text/html;charset=utf-8");
		$sdata="uid=".$this->uid."&pas=".$this->sms_password."&mob=".$phone."&type=".$this->smstype."&cid=".$this->cid."&p1=".$p1;
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
		
		$arr = json_decode($res,true);
		
		return $arr;
	}
    /**
     * 通过后台审核发送用户名以及密码
     * @param string $phone 申请者电话
     * @param string $p1 申请的用户名
     * @param string $p2 申请的密码
     */
	public function sendAshop($phone,$p1,$p2){
		//$str = mb_convert_encoding($content, "GBK", "UTF-8");
		//$sdata="method=".$this->method."&isLongSms=".$this->is_long."&username=".$this->sms_account."&password=".$this->sms_password."&smstype=".$this->smstype."&mobile=".$phone."&content=".$str;
		header("Content-type:text/html;charset=utf-8");
		$sdata="uid=".$this->uid."&pas=".$this->sms_password."&mob=".$phone."&type=".$this->smstype."&cid=".$this->cid."&p1=".$p1."&p2=".$p2;
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
	
		$arr = json_decode($res,true);
	
		return $arr;
	}
}
?> 