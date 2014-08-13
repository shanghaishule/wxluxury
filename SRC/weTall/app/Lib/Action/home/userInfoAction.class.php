<?php
class userInfoAction{
	//返回用户信息
	public function index(){
		$redirecturl = urlencode("http://www.kuyimap.com/weTall/index.php?g=home&m=userInfo&a=returnUserInfo");
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3079f89b18863917&redirect_uri=".$redirecturl."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
		//$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3079f89b18863917&redirect_uri=".$redirecturl."&response_type=code&scope=snsapi_userinfo&state=zcb#wechat_redirect";
		header("Location: ".$url);		 
	}
	
	public function returnUserInfo(){
		import('Think.ORG.Oauth2');
		$config['appId'] = "wx3079f89b18863917";
		$config['appSecret'] = "69289876b8d040b3f9a367c80f8754c8";
		if(!isset($_SESSION['uid']) || empty($_SESSION['uid']) || !isset($_SESSION['openid']) || empty($_SESSION['openid'])){
		
			if (isset($_GET['code'])){
				//echo $_GET['code'].'--';
				$Oauth = new Oauth2();
				$userinfo=$Oauth->getUserinfo($_GET['code'],$config);
				dump($userinfo);
			}else{
				echo 'no code';
			}
		}else{
			echo '1';
		}		
	}
}

?>