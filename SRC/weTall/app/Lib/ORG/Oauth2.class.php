<?php
class Oauth2{
	function getUserinfo($code,$config){
	    //echo $_GET['code'];
		//$config['appId'] = "wx3079f89b18863917";
		//$config['appSecret'] = "69289876b8d040b3f9a367c80f8754c8";
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$config['appId'].'&secret='.$config['appSecret'].'&code='.$code.'&grant_type=authorization_code';
		$request =$this->curlGet($url);
		$requestArray = json_decode($request, true);
		//var_dump($requestArray);
		$User = M('user');
		$where['wecha_id']=$requestArray['opneid'];
		$Userarr=$User->where($where)->find();
		if(!empty($Userarr)){
		return $$Userarr;	
		}else{
		$accessToken = $this->getAccessToken($config);
		$url2 = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$accessToken."&openid=".$requestArray['openid'];
		$request2 = $this->curlGet($url2);
		$requestArray2 = json_decode($request2, true);
		header('Content-Type:text/html;charset=utf-8');
		return $requestArray2;
		}
	}
	function curlGet($url){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		curl_close($ch);
		return $temp;
	
	}
	
	function getAccessToken($config) {
	
        $request =$this->curlGet('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $config['appId'] . '&secret=' . $config['appSecret']);
		$requestArray = json_decode($request, true);
        if (isset($requestArray['errcode'])) {
            return false;
        }
        $accessToken = $requestArray['access_token'];
        return $accessToken;
    }
}   
?>