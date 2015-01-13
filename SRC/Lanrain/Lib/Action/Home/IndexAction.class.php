<?php
class IndexAction extends BaseAction{
	//关注回复
	public function index(){
		if(session('uid')==""){
			$this->display("login");
		}else{
			$this->redirect(U('User/Index/index'));
		}
		
	}
	public function resetpwd(){
		$uid=$this->_get('uid','intval');
		$code=$this->_get('code','trim');
		$rtime=$this->_get('resettime','intval');
		$info=M('Users')->find($uid);
		if( (md5($info['uid'].$info['password'].$info['email'])!==$code) || ($rtime<time()) ){
			$this->error('非法操作',U('Index/index'));
		}
		$this->assign('uid',$uid);
		$this->display();
	}
	
	//忘记密码
	public function forget(){
	       if(IS_POST){
					$code = $this->_post('code','trim');
					$username = $this->_post('username','trim');
					if($code == $_SESSION['code']){
							if(!isset($_SESSION['Uphone'])){
								 $this->error("验证码已过期");
							}
							$userInfo = M('application')->where(array('uname'=>$username))->find();
							if($userInfo['phone'] == session('Uphone')){
								$sms_url = "http://api.weimi.cc/2/sms/send.html";
								$uid = "7Q30scwRSEyT";
								$sms_password = "k3a2bzn7";//密码
								$cid = 'giG8M3gmNUTw';//模板id
								$Msg = new SendMsg($sms_url, $uid, $sms_password,$cid);
								$returnMsg = $Msg ->sendAshop(session('Uphone'), $userInfo['uname'], $userInfo['password']);
								$this->success("用户名密码已发送至您的手机！");
							}else{
								$this->error("验证码已过期");
							}

					}else{
						$this->error('验证码错误！');
					}
	       }else{
	       		$this->display();
	       }  
	}
	
	//发送验证码
	public function sendCode(){
		$username = $this->_post('username','trim');
		$flag = M('application')->where(array('uname'=>$username))->find();
		if(false == $flag || $flag==''){
			 echo '0';
		}else{
			$code = '';
			for($i=1;$i<=6;$i++){
				$code.= strval(rand(0, 9));
			}
			session('code',$code);
	    	$sms_url = "http://api.weimi.cc/2/sms/send.html";
	    	$uid = "7Q30scwRSEyT";
	    	$sms_password = "k3a2bzn7";//密码
	    	$cid = 'D4M6SG46AX2y';//模板id
	    	$Msg = new SendMsg($sms_url, $uid, $sms_password,$cid);
	    	session('Uphone',$flag['phone']);
	    	$Msg->sendsms($flag['phone'],$code);
	    	echo '1';
		}
    			//$returnMsg = $Msg ->sendAshop($userIn['phone'], $userName,$password);
	}
	
	public function test(){
		require_once './Extend/PHPExcel_1.7.9/Classes/PHPExcel/IOFactory.php';
		
		if (!file_exists("./Extend/PHPExcel_1.7.9/Examples/01simple.xls")) {
			exit("file not exist.");
		}
		
		$objPHPExcel = PHPExcel_IOFactory::load("./Extend/PHPExcel_1.7.9/Examples/01simple.xls");
		
		dump($objPHPExcel);
		
		die('xxxx');
	}
}