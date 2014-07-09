<?php
class applicationAction extends frontendAction{
	public $application_mod;
	
	public function _initialize(){
		parent::_initialize();
		$this->application_mod = M('application');
	}
	public function index_redirect(){
		$redirecturl = urlencode("http://www.kuyimap.com/weTall/index.php?g=home&m=application&a=index");
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3079f89b18863917&redirect_uri=".$redirecturl."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
		//$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3079f89b18863917&redirect_uri=".$redirecturl."&response_type=code&scope=snsapi_userinfo&state=zcb#wechat_redirect";
		header("Location: ".$url);
	}
	public function index(){
		import('Think.ORG.Oauth2');
		$config['appId'] = "wx3079f89b18863917";
		$config['appSecret'] = "69289876b8d040b3f9a367c80f8754c8";
		if(!isset($_SESSION['uid']) || $_SESSION['uid']==''){
			 
			if (isset($_GET['code'])){
				//echo $_GET['code'].'--';
				$Oauth = new Oauth2();
				$userinfo=$Oauth->getUserinfo($_GET['code'],$config);
				//dump($userinfo);exit;
				$userinfo['last_login_time']=time();
				$userinfo['last_login_ip']=get_client_ip();
				
				$Userarr= M('user')->where(array('openid'=>$userinfo['openid']))->find();
				if(!empty($Userarr) && $Userarr!=''){
					$_SESSION['uid']=$Userarr['id'];
					$_SESSION['name']=$Userarr['nickname'];
					$_SESSION['user_info']=$Userarr;
					//dump($Userarr['openid']);exit;
				}else{
					$_SESSION['uid']=M('user')->add($userinfo);
					$_SESSION['name']=$userinfo['nickname'];
					$_SESSION['user_info']=$userinfo;
					//dump($userinfo['openid']);exit;
				}
				// dump($_SESSION['uid'].'-1-'.$_SESSION['name']);exit;
			}else{
				$this->error('页面异常',"{:U(index/brandshop)}");
			} 
		}
		//$user = $this->_session('user_info');
		$user = $_SESSION['user_info'];
		if ($user) {
			$uid = $_SESSION['uid'];
			$username = $_SESSION['nickname'];
			//$umail = $user['email'];
			$umail='';
		}else{
			$uid = '0';
			$username = '匿名';
			$umail = '';
		}
		
		//dump($user);exit;
		$this->assign('uid',$uid);
		$this->assign('username',$username);
		$this->assign('umail',$umail);
		
		$createtime = time();
		$this->assign('createtime',$createtime);
		$tokenTall = $this->getTokenTall();
		$this->assign('tokenTall',$tokenTall);
		
		//$wecha_id = $this->getWechaId();
		$wecha_id = $user['openid'];
		//dump($wecha_id);exit;
		$this->assign('wecha_id',$wecha_id);
		
		if($this->application_mod->where(array('wecha_id'=>$wecha_id))->find()){
			$this->assign('apply_again','1');
		}else{
			$this->assign('apply_again','0');
		}
		//品牌
		
		$brand = M("brandlist")->select();
		$this->assign("brand",$brand);
		$this->assign("title","实体店入驻");
		//dump($_SESSION);exit;
		$this->display();
	}	

	public function addressselect(){
		$upload_shop = M("upload_shop");
		$select_brand = $this->_get("name","trim");
		$where["brand_name"] = $select_brand;
		$result = $upload_shop->where($where)->distinct(true)->field("provice")->select();
		if ($result){
			// 成功后返回客户端新增的用户ID，并返回提示信息和操作状态
			$_SESSION["select_brand"] = $select_brand;
			$this->ajaxReturn($result,"新增成功！",1);
		}else{
			// 错误后返回错误的操作状态和提示信息
			$this->ajaxReturn(0,"新增错误！",0);
		}
	}
	
	public function proviceselect(){
		$upload_shop = M("upload_shop");
		$where["brand_name"] = $_SESSION["select_brand"];
		$where["provice"] = $this->_get("name","trim");
		$result = $upload_shop->where($where)->distinct(true)->field("city")->select();
		if ($result){
			// 成功后返回客户端新增的用户ID，并返回提示信息和操作状态
			$_SESSION["select_provice"] = $where["provice"];
			$this->ajaxReturn($result,"新增成功！",1);
		}else{
			// 错误后返回错误的操作状态和提示信息
			$this->ajaxReturn(0,"新增错误！",0);
		}
	}
	public function cityselect(){
		$upload_shop = M("upload_shop");
		$where["brand_name"] = $_SESSION["select_brand"];
		$where["provice"] = $_SESSION["select_provice"];
		$where["city"] =  $this->_get("name","trim");
		$result = $upload_shop->where($where)->distinct(true)->field("shop_name")->select();
		if ($result){
			// 成功后返回客户端新增的用户ID，并返回提示信息和操作状态
			$this->ajaxReturn($result,"新增成功！",1);
		}else{
			// 错误后返回错误的操作状态和提示信息
			$this->ajaxReturn(0,"新增错误！",0);
		}
	}
	public function add(){
		if($_POST){
			if ($this->application_mod->create()) {
				if($this->application_mod->add()){
					echo '您的开店申请已经成功提交！我们会尽快与您联系！';
				}else{
					echo '很遗憾，您的申请失败了！';
				}
			}
		}
	}
	
	public function showinfo(){
		$application = $this->application_mod->select();
		$this->assign('application',$application);
		$this->display();
	}
}
?>