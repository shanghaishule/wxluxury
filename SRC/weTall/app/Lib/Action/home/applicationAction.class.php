<?php
class applicationAction extends frontendAction{
	public $application_mod;
	
	public function _initialize(){
		parent::_initialize();
		$this->application_mod = M('application');
	}

	public function index(){
		$user = $this->_session('user_info');
		if ($user) {
			$uid = $user['id'];
			$username = $user['username'];
			$umail = $user['email'];
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
		
		$wecha_id = $this->getWechaId();
		$this->assign('wecha_id',$wecha_id);
		
		if($this->application_mod->where(array('wecha_id'=>$wecha_id))->find()){
			$this->assign('apply_again','1');
		}else{
			$this->assign('apply_again','0');
		}
		//品牌
		
		$brand = M("brandlist")->select();
		$this->assign("brand",$brand);
		//dump($_SESSION);exit;
		$this->display();
	}

	public function addressselect(){
		$upload_shop = M("upload_shop");
		$where["brand_name"] = $this->_get("name","trim");
		$result = $upload_shop->where($where)->select();
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