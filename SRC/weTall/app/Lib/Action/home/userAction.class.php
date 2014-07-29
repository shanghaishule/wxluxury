<?php
class userAction extends userbaseAction {
	/*public $commetns_mod;
	public function _initialize(){
		parent::_initialize();
		$this->commetns_mod = M('comments');
	}
	*/
	public function ajaxlogin()
    {
       $user_name=$_POST['user_name'];
       $password=$_POST['password'];
       
       $user=M('user');
       $users= $user->field('id,username')->where("username='".$user_name."' and password='".md5($password)."'")->find(); 
       if(is_array($users))
       {
    	$data = array('status'=>1);
    	
       }else {
       	$data = array('status'=>0);
       }
    	
    	echo json_encode($data);
    	exit;
    
    }
	
    /**
     * 用户登陆
     */
    public function login() {
    	$tokenTall = $this->getTokenTall();
    	$this->assign('tokenTall', $tokenTall);
        $this->visitor->is_login && $this->redirect('user/index', array('tokenTall'=>$tokenTall));
        if (IS_POST) {
            $username = $this->_post('user_name', 'trim');
            $password = $this->_post('password', 'trim');
            $remember = $this->_post('remember');
            if (empty($username)) {
                IS_AJAX && $this->ajaxReturn(0, L('please_input').L('password'));
                $this->error(L('please_input').L('username'));
            }
            if (empty($password)) {
                IS_AJAX && $this->ajaxReturn(0, L('please_input').L('password'));
                $this->error(L('please_input').L('password'));
            }
            //连接用户中心
            $passport = $this->_user_server();
            $uid = $passport->auth($username, $password);
            if (!$uid) {
                IS_AJAX && $this->ajaxReturn(0, $passport->get_error());
                $this->error($passport->get_error());
            }
            //登陆
            $this->visitor->login($uid, $remember);
            //登陆完成钩子
            $tag_arg = array('uid'=>$uid, 'username'=>$username, 'action'=>'login');
            //tag('login_end', $tag_arg);
            
            //同步登陆
            $synlogin = $passport->synlogin($uid);
            if (IS_AJAX) {
                $this->ajaxReturn(1, L('login_successe').$synlogin);
            } else {
                //跳转到登陆前页面（执行同步操作）
                $ret_url = $this->_post('ret_url', 'trim');
                $this->success(L('login_successe').$synlogin, $ret_url);
            }
        } else {
            /* 同步退出外部系统 */
            if (!empty($_GET['synlogout'])) {
                $passport = $this->_user_server();
                $synlogout = $passport->synlogout();
            }
            if (IS_AJAX) {
                $resp = $this->fetch('dialog:login');
                $this->ajaxReturn(1, '', $resp);
            } else {
                //来路
                $ret_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __APP__;
                $this->assign('ret_url', $ret_url);
                $this->assign('synlogout', $synlogout);
                $this->_config_seo();
                $this->display();
            }
        }
    }

    public function addaddress()
    {
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	//取商家token值，取不到则默认为空
    	$tokenTall = $this->getTokenTall();

    	if(IS_POST)
    	{
	    	$user_address = M('user_address');
	    	
	    	$consignee= $this->_post('consignee', 'trim');
	    	$sheng= $this->_post('sheng', 'trim');
	    	$shi= $this->_post('shi', 'trim');
	    	$qu= $this->_post('qu', 'trim');
	    	$address= $this->_post('address', 'trim');
	    	$phone_mob= $this->_post('phone_mob', 'trim');
	    	
	    	$data['uid']=$_SESSION['uid'];
	    	$data['consignee']=$consignee;
	        $data['sheng']=$sheng;
	    	$data['shi']=$shi;
	    	$data['qu']=$qu;
	    	$data['address']=$address;
	    	$data['mobile']=$phone_mob;
    	
    		//echo $_SESSION['uid'];
    	
	        if($user_address->data($data)->add()!==false)
	        {
	        	$this->redirect('user/address',array('tokenTall'=>$tokenTall));
	        }
    	}
    	$this->assign('tokenTall',$tokenTall);
    	$this->display();
    }

    /**
     * 用户退出
     */
    public function logout() {
        $this->visitor->logout();
        //同步退出
        $passport = $this->_user_server();
        $synlogout = $passport->synlogout();
        //跳转到退出前页面（执行同步操作）
        //取商家token值，取不到则默认为空
        $tokenTall = $this->getTokenTall();
        $this->assign('tokenTall',$tokenTall);
        $this->success(L('logout_successe').$synlogout, U('user/index',array('tokenTall'=>$tokenTall)));
    }

    /**
     * 用户绑定
     */
    public function binding() {
        $user_bind_info = object_to_array(cookie('user_bind_info'));
        $this->assign('user_bind_info', $user_bind_info);
        $this->_config_seo();
        $this->display();
    }

    /**
    * 用户注册
    */
    public function register() {
    	$tokenTall = $this->getTokenTall();
        $this->visitor->is_login && $this->redirect('user/index', array('tokenTall'=>$tokenTall));
        if (IS_POST) {
            $username = $this->_post('user_name', 'trim');
            $email = $this->_post('email','trim');
            $password = $this->_post('password', 'trim');
            $repassword = $this->_post('password_confirm', 'trim');
            if ($password != $repassword) {
                $this->error(L('inconsistent_password')); //确认密码
            }
            $gender = $this->_post('gender','intval', '0');
            //用户禁止
            $ipban_mod = D('ipban');
            $ipban_mod->clear(); //清除过期数据
            $is_ban = $ipban_mod->where("(type='name' AND name='".$username."') OR (type='email' AND name='".$email."')")->count();
            $is_ban && $this->error(L('register_ban'));
         
            //连接用户中心
            $passport = $this->_user_server();
            //注册
            $uid = $passport->register($username, $password, $email, $gender);
            !$uid && $this->error($passport->get_error());

            //注册完成钩子
            $tag_arg = array('uid'=>$uid, 'uname'=>$username, 'action'=>'register');
            //tag('register_end', $tag_arg);
            
            //登陆
            $this->visitor->login($uid);
            
            //登陆完成钩子
            $tag_arg = array('uid'=>$uid, 'uname'=>$username, 'action'=>'login');
            //tag('login_end', $tag_arg);
            
            //同步登陆
            $synlogin = $passport->synlogin($uid);
            $this->redirect('user/index', array('tokenTall'=>$tokenTall));
           // $this->success(L('register_successe').$synlogin, U('user/index'));

            exit;
        } else {
            //关闭注册
            if (!C('pin_reg_status')) {
                $this->error(C('pin_reg_closed_reason'));
            }
            $this->_config_seo();
            $this->display();
        }
    }

    /**
     * 第三方头像保存
     */
    private function _save_avatar($uid, $img) {
        //获取后台头像规格设置
        $avatar_size = explode(',', C('pin_avatar_size'));
        //会员头像保存文件夹
        $avatar_dir = C('pin_attach_path') . 'avatar/' . avatar_dir($uid);
        !is_dir($avatar_dir) && mkdir($avatar_dir,0777,true);
        //生成缩略图
        $img = C('pin_attach_path') . 'avatar/temp/' . $img;
        foreach ($avatar_size as $size) {
            Image::thumb($img, $avatar_dir.md5($uid).'_'.$size.'.jpg', '', $size, $size, true);
        }
        @unlink($img);
    }
    
    /**
     * 用户消息提示 
     */
    public function msgtip() {
       // $result = D('user_msgtip')->get_list($_SESSION['uid']);
        $result = D('user_msgtip')->get_list($_SESSION['uid']);
        $this->ajaxReturn(1, '', $result);
    }
    public function logintest(){
    	$redirecturl = urlencode("http://www.kuyimap.com/weTall/index.php?g=home&m=user&a=index");
    	$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3079f89b18863917&redirect_uri=".$redirecturl."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
    	//$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3079f89b18863917&redirect_uri=".$redirecturl."&response_type=code&scope=snsapi_userinfo&state=zcb#wechat_redirect";
    	header("Location: ".$url);
    }
    /**
    * 基本信息修改
    */
    public function index() {
    	//取商家token值，取不到则默认为空
    	import('Think.ORG.Oauth2');
    	$config['appId'] = "wx3079f89b18863917";
    	$config['appSecret'] = "69289876b8d040b3f9a367c80f8754c8";
    	if(!isset($_SESSION['uid']) && empty($_SESSION['uid'])){

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
	    		$_SESSION['headimgurl']=$Userarr['headimgurl'];
	    		$_SESSION['openid']=$userinfo['openid'];
	    	}else{
	    		$_SESSION['uid']=M('user')->add($userinfo);
	    		$_SESSION['name']=$userinfo['nickname'];
	    		$_SESSION['headimgurl']=$Userarr['headimgurl'];
	    		$_SESSION['openid']=$userinfo['openid'];
	    	}
    	   // dump($_SESSION['uid'].'-1-'.$_SESSION['name']);exit;		
	    	}else{
	    	$this->error('页面已过期',U("index/brandshop"));
	    	}
    	   
   	 	}	
   	 	//dump($_SESSION['uid'].'-2-'.$_SESSION['name']);exit;
    	$tokenTall = $this->getTokenTall();
    	
        $item_order=M('item_order');
        
        $order_detail=M('order_detail');
        
        if(!isset($_GET['status']))
        {
      	    $status=1;
        }
        else
        {
      	    $status=$_GET['status'];
        }
      
        $item_orders= $item_order->order('id desc')->where(array('status'=>$status,userId=>$_SESSION['uid']))->select();
        foreach ($item_orders as $key=>$val)
        {
      		$order_details = $order_detail->where("orderId='".$val['orderId']."'")->select();
	      	foreach ($order_details as $val)
	      	{
	      		$items = array('title'=>$val['title'],'img'=>$val['img'],'price'=>$val['price'],'quantity'=>$val['quantity'],'itemId'=>$val['itemId'],'size'=>$val['size'],'color'=>$val['color']);
	      		$item_orders[$key]['items'][] = $items;
	      	}
        }
      
       $this->assign('item_orders',$item_orders);
       $this->assign("title","我的资料");
       $this->assign('status',$status);
       $this->assign('tokenTall',$tokenTall);
       $this->_config_seo();
       $this->display();
    }

    /**
     * 修改头像
     */
    public function upload_avatar() {

        if (!empty($_FILES['avatar']['name'])) {
            //会员头像规格
            $avatar_size = explode(',', C('pin_avatar_size'));
            //回去会员头像保存文件夹
            $uid = abs(intval($_SESSION['uid']));
            $suid = sprintf("%09d", $uid);
            $dir1 = substr($suid, 0, 3);
            $dir2 = substr($suid, 3, 2);
            $dir3 = substr($suid, 5, 2);
            $avatar_dir = $dir1.'/'.$dir2.'/'.$dir3.'/';
            //上传头像
            $suffix = '';
            foreach ($avatar_size as $size) {
                $suffix .= '_'.$size.',';
            }
            $result = $this->_upload($_FILES['avatar'], 'avatar/'.$avatar_dir, array(
                'width'=>C('pin_avatar_size'), 
                'height'=>C('pin_avatar_size'),
                'remove_origin'=>true, 
                'suffix'=>trim($suffix, ','),
                'ext' => 'jpg',
            ), md5($uid));
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
                $data = __ROOT__.'/data/upload/avatar/'.$avatar_dir.md5($uid).'_'.$size.'.jpg?'.time();
                $this->ajaxReturn(1, L('upload_success'), $data);
            }
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

    /**
     * 修改密码
     */
    public function password() {
        if( IS_POST ){
            $oldpassword = $this->_post('oldpassword','trim');
            $password   = $this->_post('password','trim');
            $repassword = $this->_post('repassword','trim');
            !$password && $this->error(L('no_new_password'));
            $password != $repassword && $this->error(L('inconsistent_password'));
            $passlen = strlen($password);
            if ($passlen < 6 || $passlen > 20) {
                $this->error('password_length_error');
            }
            //连接用户中心
            $passport = $this->_user_server();
            $result = $passport->edit($_SESSION['uid'], $oldpassword, array('password'=>$password));
            if ($result) {
                $msg = array('status'=>1, 'info'=>L('edit_password_success'));
            } else {
                $msg = array('status'=>0, 'info'=>$passport->get_error());
            }
            $this->assign('msg', $msg);
        }
        $this->_config_seo();
        $this->display();
    }

    /**
     * 帐号绑定
     */
    public function bind() {
        //获取已经绑定列表
        $bind_list = M('user_bind')->field('type')->where(array('uid'=>$_SESSION['uid']))->select();
        $binds = array();
        if ($bind_list) {
            foreach ($bind_list as $val) {
                $binds[] = $val['type'];
            }
        }
        
        //获取网站支持列表
        $oauth_list = $this->oauth_list;
        foreach ($oauth_list as $type => $_oauth) {
            $oauth_list[$type]['isbind'] = '0';
            if (in_array($type, $binds)) {
                $oauth_list[$type]['isbind'] = '1';
            }
        }
        $this->assign('oauth_list', $oauth_list);
        $this->_config_seo();
        $this->display();
    }

    /**
     * 个人空间banner背景设置
     */
    public function custom() {
        $cover = $this->visitor->get('cover');
        $this->assign('cover', $cover);
        $this->_config_seo();
        $this->display();
    }

    /**
     * 取消封面
     */
    public function cancle_cover() {
        $result = M('user')->where(array('id'=>$_SESSION['uid']))->setField('cover', '');
        !$result && $this->ajaxReturn(0, L('illegal_parameters'));
        $this->ajaxReturn(1, L('edit_success'));
    }

    /**
     * 上传封面图片
     */
    public function upload_cover() {
        if (!empty($_FILES['cover']['name'])) {
            $data_dir = date('ym/d');
            $file_name = md5($_SESSION['uid']);
            $result = $this->_upload($_FILES['cover'], 'cover/'.$data_dir, array('width'=>'900', 'height'=>'330', 'remove_origin'=>true), $file_name);
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $cover = $data_dir.'/'.$file_name.'_thumb.'.$ext;
                $data = '<img src="./data/upload/cover/'.$data_dir.'/'.$file_name.'_thumb.'.$ext.'?'.time().'">';
                //更新数据
                M('user')->where(array('id'=>$_SESSION['uid']))->setField('cover', $cover);
                $this->ajaxReturn(1, L('upload_success'), $data);
            }
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

    
    public function edit_address()
    {
    	
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	
        $user_address_mod = M('user_address');
        $id = $this->_get('id', 'intval');
        $info = $user_address_mod->find($id);
        $this->assign('info', $info);
        //取商家token值，取不到则默认为空
        $tokenTall = $this->getTokenTall();
        $this->assign('tokenTall',$tokenTall);
    	$this->display();
    }
    
    /**
     * 收货地址
     */
    public function address() {
    	
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
        $user_address_mod = M('user_address');
        $id = $this->_get('id', 'intval');
        $type = $this->_get('type', 'trim', 'edit');
        if ($id) {
            if ($type == 'del') {
                $user_address_mod->where(array('id'=>$id, 'uid'=>$_SESSION['uid']))->delete();
                $msg = array('status'=>1, 'info'=>L('delete_success'));
                $this->assign('msg', $msg);
            } else {
                $info = $user_address_mod->find($id);
                $this->assign('info', $info);
            }
        }
        if (IS_POST) {
            $consignee = $this->_post('consignee', 'trim');
            $address = $this->_post('address', 'trim');
         	//   $zip = $this->_post('zip', 'trim');
	        $mobile = $this->_post('phone_mob', 'trim');
	        $sheng = $this->_post('sheng', 'trim');
	        $shi = $this->_post('shi', 'trim');
	        $qu = $this->_post('qu', 'trim');
            $id = $this->_post('id', 'intval');
            if ($id and $address) {
                $result = $user_address_mod->where(array('id'=>$id, 'uid'=>$_SESSION['uid']))->save(array(
                    'consignee' => $consignee,
                    'address' => $address,
                    // 'zip' => $zip,
                    'mobile' => $mobile,
                    'sheng' => $sheng,
                    'shi' => $shi,
                    'qu' => $qu,
                ));
                if ($result) {
                    $msg = array('status'=>1, 'info'=>L('edit_success'));
                } else {
                    $msg = array('status'=>0, 'info'=>L('edit_failed'));
                }
            } else {
                $result = $user_address_mod->add(array(
                    'uid' => $_SESSION['uid'],
                    'consignee' => $consignee,
                    'address' => $address,
                    'zip' => $zip,
                    'mobile' => $mobile,
                ));
                if ($result) {
                    $msg = array('status'=>1, 'info'=>L('add_address_success'));
                } else {
                    $msg = array('status'=>0, 'info'=>L('add_address_failed'));
                }
            }
            $this->assign('msg', $msg);
        }
        
        $address_list = $user_address_mod->where(array('uid'=>$_SESSION['uid']))->select();
        $this->assign('address_list', $address_list);
        //取商家token值，取不到则默认为空
        $tokenTall = $this->getTokenTall();
        $this->assign('tokenTall',$tokenTall);
        
        $this->_config_seo();
        $this->display();
    }
   
    /**
     * 检测用户
     */
    public function ajax_check() {
        $type = $this->_get('type', 'trim', 'email');
        $user_mod = D('user');
        switch ($type) {
            case 'email':
                $email = $this->_get('J_email', 'trim');
                $user_mod->email_exists($email) ? $this->ajaxReturn(0) : $this->ajaxReturn(1);
                break;
            
            case 'username':
                $username = $this->_get('J_username', 'trim');
                $user_mod->name_exists($username) ? $this->ajaxReturn(0) : $this->ajaxReturn(1);
                break;
        }
    }

    /**
     * 关注
     */
    public function follow() {
        $uid = $this->_get('uid', 'intval');
        !$uid && $this->ajaxReturn(0, L('follow_invalid_user'));
        $uid == $_SESSION['uid'] && $this->ajaxReturn(0, L('follow_self_not_allow'));
        $user_mod = M('user');
        if (!$user_mod->where(array('id'=>$uid))->count('id')) {
            $this->ajaxReturn(0, L('follow_invalid_user'));
        }
        $user_follow_mod = M('user_follow');
        //已经关注？
        $is_follow = $user_follow_mod->where(array('uid'=>$_SESSION['uid'], 'follow_uid'=>$uid))->count();
        $is_follow && $this->ajaxReturn(0, L('user_is_followed'));
        //关注动作
        $return = 1;
        //他是否已经关注我
        $map = array('uid'=>$uid, 'follow_uid'=>$_SESSION['uid']);
        $isfollow_me = $user_follow_mod->where($map)->count();
        $data = array('uid'=>$_SESSION['uid'], 'follow_uid'=>$uid, 'add_time'=>time());
        if ($isfollow_me) {
            $data['mutually'] = 1; //互相关注
            $user_follow_mod->where($map)->setField('mutually', 1); //更新他关注我的记录为互相关注
            $return = 2;
        }
        $result = $user_follow_mod->add($data);
        !$result && $this->ajaxReturn(0, L('follow_user_failed'));
        //增加我的关注人数
        $user_mod->where(array('id'=>$_SESSION['uid']))->setInc('follows');
        //增加Ta的粉丝人数
        $user_mod->where(array('id'=>$uid))->setInc('fans');
        //提醒被关注的人
        D('user_msgtip')->add_tip($uid, 1);
        //把他的微薄推送给我
        //TODO...是否有必要？
        $this->ajaxReturn(1, L('follow_user_success'), $return);
    }

    /**
     * 取消关注
     */
    public function unfollow() {
        $uid = $this->_get('uid', 'intval');
        !$uid && $this->ajaxReturn(0, L('unfollow_invalid_user'));
        $user_follow_mod = M('user_follow');
        if ($user_follow_mod->where(array('uid'=>$_SESSION['uid'], 'follow_uid'=>$uid))->delete()) {
            $user_mod = M('user');
            //他是否已经关注我
            $map = array('uid'=>$uid, 'follow_uid'=>$_SESSION['uid']);
            $isfollow_me = $user_follow_mod->where($map)->count();
            if ($isfollow_me) {
                $user_follow_mod->where($map)->setField('mutually', 0); //更新他关注我的记录为互相关注
            }
            //减少我的关注人数
            $user_mod->where(array('id'=>$_SESSION['uid']))->setDec('follows');
            //减少Ta的粉丝人数
            $user_mod->where(array('id'=>$uid))->setDec('fans');
            //删除我微薄中Ta的内容
            M('topic_index')->where(array('author_id'=>$uid, 'uid'=>$_SESSION['uid']))->delete();
            $this->ajaxReturn(1, L('unfollow_user_success'));
        } else {
            $this->ajaxReturn(0, L('unfollow_user_failed'));
        }
    }

    /**
     * 移除粉丝
     */
    public function delfans() {
        $uid = $this->_get('uid', 'intval');
        !$uid && $this->ajaxReturn(0, L('delete_invalid_fans'));
        $user_follow_mod = M('user_follow');
        if ($user_follow_mod->where(array('follow_uid'=>$_SESSION['uid'], 'uid'=>$uid))->delete()) {
            $user_mod = M('user');
            //减少我的粉丝人数
            $user_mod->where(array('id'=>$_SESSION['uid']))->setDec('fans');
            //减少Ta的关注人数
            M('user')->where(array('id'=>$uid))->setDec('follows');
            //删除Ta微薄中我的内容
            M('topic_index')->where(array('author_id'=>$_SESSION['uid'], 'uid'=>$uid))->delete();
            $this->ajaxReturn(1, L('delete_fans_success'));
        } else {
            $this->ajaxReturn(0, L('delete_fans_failed'));
        }
    }
    
    /**
     * 我的收藏
     */
    public function favi() {
    	//取商家token值，取不到则默认为空
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	
    	$tokenTall = $this->getTokenTall();
    	
    	//$favi_mod = M('shop_favi');
    	//$favi_list = $favi_mod->where(array('userid'=>$_SESSION['uid']))->select();
    	$userid = $_SESSION['uid'];
    	/*店铺信息*/
    	$model=new Model();
    	$weChaShop = $model->table('tp_shop_favi a, tp_item b')
    	->where("a.item_id = b.id and a.userid='".$userid."'")
    	->field("b.*, '".$tokenTall."' url")
    	->select();
    	//dump($weChaShop);exit;
    	$this->assign("weshopData",$weChaShop);

    	$this->assign("title","我的收藏");
    	$this->assign('tokenTall',$tokenTall);
    	$this->display();
    }
    
    /**
     * 我的品牌积分
     */
    public function jifen() {
    	   	
    	//取商家token值，取不到则默认为空
    	$tokenTall = $this->getTokenTall();    	
    	//$favi_mod = M('shop_favi');
    	//$favi_list = $favi_mod->where(array('userid'=>$_SESSION['uid']))->select();
    	$where["id"] = $_SESSION['uid'];
    	/*店铺信息*/
    	$model=M("user")->where($where)->find();
    	$weChaShop=explode(",",$model["brand_jifen"]);
    	$jifen_array=array();
    	foreach ($weChaShop as $detail_jifen){
    		  $jifen=explode("|",$detail_jifen);
    		if ($jifen[0] != ""){
	    		$brand_data1["id"] = $jifen[0];
	    		$brand_fenzhi1 = M("brandlist")->where($brand_data1)->find();
	    		$brand_data[0] = $brand_fenzhi1["name"];
	    		$brand_data[1] = $jifen[1];
	    		$jifen_array[]=$brand_data;
    		}
    	}
    	if (count($jifen_array) != 0) {
    		$this->assign("jifen_array",$jifen_array);
    	}
    	//dump($weChaShop);exit;
        $this->assign("title","我的积分");
    	$this->assign('tokenTall',$tokenTall);
    	$this->display();
    }
    
    /**
     *我的品牌积分
     */
    public function my_jifen(){
    	$tokenTall = $this->getTokenTall();
    	$this->assign('tokenTall',$tokenTall);
    	$where["uid"] = $_SESSION['uid'];
    	$mypoints = M("brandpoints")->where($where)->select();
    	$array_mypoints = array();
    	foreach ($mypoints as $key){
    		$array_mypoints[$key['id']]["points"] = $key["points"];
    		$array_mypoints[$key['id']]["used_points"] = $key["used_points"];
    		$array_mypoints[$key['id']]["num"] = $key['num'];
    		$brand_info = M("brandlist")->where(array("id"=>$key['brandid']))->find();
    		$array_mypoints[$key['id']]["imgurl"] = $brand_info["imgurl"];
    		$array_mypoints[$key['id']]["name"] = $brand_info["name"];
    	}
    	//dump($array_mypoints);exit;
    	$this->assign("my_points",$array_mypoints);
    	$this->assign("title","我的积分");
    	$this->display("jifen");
    	
    }
     /**
     *	追加评论
     */
    public function comments(){
    /*
        $data['user_comments']= $this->_get('user_comments', 'intval');        
    	$data['item_id'] = $_SESSION['item_id'] ;
        $data['user_name'] = $this->visitor->info['username'];
		$data['create_time'] = date('y-m-d H:i:m');
		$record= M('comments');
		$record->add($data);
    	$username = $this->visitor->info['username'];
    	$createtime = date('y-m-d H:i:m');
    */
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	$item = $this->_get('item');    	    	
    	$this->assign('item',$item);
    	$this->assign('username',$this->visitor->info['username']);
    	$this->assign('createtime',date('y-m-d H:i:m'));
    	$this->assign('tokenTall',$this->getTokenTall());
        $this->display();
    } 
    
    /**
     *	插入评论
     */
    public function addcomm() {
     if($_POST){
		if (M('commetns_mod')->create()) {
			if(M('commetns_mod')->add()){
				echo '您的评论已经成功提交！';
			}else{
				echo '很遗憾，您的评论提交失败了！';
			}
		}
     	
	}
		
    }  
    
    public function mymatch() {
    	$uid = $_SESSION['uid'];
    	$m=M();
    	$Sel_sql = "SELECT * from tp_match where is_send = 1 & uid = ".$uid;
    	$result=$m->query($Sel_sql);
    	$item_favi_detail = M("item");
    	$match_table = array();
    	$id=0;
    
    	$match_favi = array();
    	foreach ($result as $match_result){
    		$match_table[] = $match_result;
    		$username = M("user")->where("id=".$match_result["uid"])->find();
    		$match_table[$id]["uname"] = $username["username"];
    		$id ++;
    		if ($match_result != "" or $match_result != null) {
    			$item_favi = explode(",", $match_result["item_ids"]);
    			foreach ($item_favi as $val){
    				$match_favi_sequence["id"] = $match_result["id"];
    				$item = $item_favi_detail->where("id=".$val)->find();
    				$match_favi_sequence["favi_name"] = $item["title"];
    				$match_favi_sequence["favi_img"] = $item["img"];
    				$match_favi_sequence["favi_price"] = $item["price"];
    				$match_favi[] = $match_favi_sequence;
    			}
    		}
    	}
    	//var_dump($match_table);die();
    	$this->assign("match_table",$match_table);
    	$this->assign("favi_table",$match_favi);
    	$this->display();
    }
    public function share_save(){
    
    	$share_id["id"] = $this->_get("id","intval");
    	$is_send["is_send"] = 1;
    	$match = M("match")->where($share_id)->save($is_send);
    	if($match){
    		$this->ajaxReturn(1,"已经分享！",1);
    	}else{
    		$this->ajaxReturn(0,"分享失败！",0);
    	}
    }
    public function mysave() {
    	$uid = $_SESSION['uid'];
    	$m=M();
    	$Sel_sql = "SELECT * from tp_match where is_send = 2 & uid = ".$uid;
    	$result=$m->query($Sel_sql);
    	$item_favi_detail = M("item");
    	$match_table = array();
    	$id=0;
    
    	$match_favi = array();
    	foreach ($result as $match_result){
    		$match_table[] = $match_result;
    		$username = M("user")->where("id=".$match_result["uid"])->find();
    		$match_table[$id]["uname"] = $username["username"];
    		$id ++;
    		if ($match_result != "" or $match_result != null) {
    			$item_favi = explode(",", $match_result["item_ids"]);
    			foreach ($item_favi as $val){
    				$match_favi_sequence["id"] = $match_result["id"];
    				$item = $item_favi_detail->where("id=".$val)->find();
    				$match_favi_sequence["favi_name"] = $item["title"];
    				$match_favi_sequence["favi_img"] = $item["img"];
    				$match_favi_sequence["favi_price"] = $item["price"];
    				$match_favi[] = $match_favi_sequence;
    			}
    		}
    	}
    	//var_dump($match_table);die();
    	$this->assign("match_table",$match_table);
    	$this->assign("favi_table",$match_favi);
    	$this->display();
    }
    public function addMatch() {
    
    	$tokenTall = $this->getTokenTall();
    	//$uid = $_SESSION['uid','1'];
    	$uid =$_SESSION['uid'];
    	$where['is_send'] = "0";
    	$where['uid'] = $uid;
    	 
    	//获得图片和title
    	$m=M();
    	$Sel_sql = "SELECT i.title, i.img,s.item_id FROM tp_item i, tp_shop_favi s ";
    	$Where_sql = "WHERE i.id = s.item_id and s.userid = ".$uid;
    	$result=$m->query($Sel_sql.$Where_sql);
    
    	//items 设定
    	//$math_data = M("match")->where($where)->find();
    	$math_data = $_SESSION['math_data'];
    	$this->assign("user_info",$result);
    	$this->assign("math_data",$math_data);
    
    	if(IS_POST){
    		//for the select item
    		$data = array();
    
    		//uploadfile
    		$Uninum = time();
    		$filepath = $_SERVER['DOCUMENT_ROOT']."/Uploads/items/images/";//图片保存的路径目录
    		if(!is_dir($filepath)){
    			mkdir($filepath,0777, true);
    		}
    		$file_type = explode(".",$_POST['img_name']);
    		$filename = $Uninum.'.'.$file_type[1]; //生成文件名
    		move_uploaded_file($_FILES["my_img"]["tmp_name"],$filepath.$filename);
    		$data['upd_path'] = '/Uploads/items/images/'.$filename;
    
    		$item_ids = "";
    		foreach($result as $val){
    			if($_POST['txt_'.$val['item_id']] == "1"){
    				$item_ids = $item_ids.$val['item_id'].",";
    			}
    		}
    		$item_ids = substr($item_ids,0,strlen($item_ids)-1);
    		$data['item_ids'] = $item_ids;
    		$data['uid'] = $uid;
    		$data['my_img'] = $_POST['img_name'];
    		$data['title'] = $_POST['title'];
    		$data['is_send'] = $_POST['is_send'];
    		if($data['is_send'] != "0"){
    			$data['create_time'] = time();
    			 
    		}
    		 
    		$math_data = M("match")->where($where)->find();
    		if($math_data == NULL){
    			M("match")->add($data);
    		}else{
    			M("match")->where($where)->save($data);
    		}
    
    		$_SESSION['math_data'] = $math_data;
    		//
    		if($data['is_send'] == "0"){
    				$this->success('保存成功！');
    	}else{
    		$this->success('发稿成功！');
    		}
    
    		}
    
    		$this->display();
    }
 
    public function preMatch() {
    
    //$uid = $_SESSION['uid'];
    $uid =$_SESSION['uid'];
    //uploadfile
    $data['upd_path'] = $this->getUploadFile();

    //取得所以收藏
    $result = $this->getUserFavi();
    //取得所选收藏
    $item_ids = $this->getSelFavi($result);
    
    $data['item_ids'] = $item_ids;
    $data['uid'] = $uid;
    $data['my_img'] = $_POST['img_name'];
    $data['title'] = $_POST['title'];
    $data['is_send'] = $_POST['is_send'];
    $this->assign("math_data",$data);
    $m=M();
    $Sel_sql = "SELECT * from tp_match where is_send in ('0','2') and uid =".$uid ;
    $result=$m->query($Sel_sql);
    //dump($result);
    
    if($result == NULL){
    	$data['is_send'] = "2";
    	M("match")->add($data);
    }else{
    //dump("2");
    $data['is_send'] = "2";
    $where['is_send'] = $result['is_send'];
    $where['uid'] = $uid;
    M("match")->where($where)->save($data);
    }
    //item_ids
    $item_array = explode(",",$item_ids);
    $this->assign("item_ids",$item_array);
    
    	//获得图片和title
    $result = $this->getMatchItem($item_ids);
    $this->assign("item",$result);
    
    $this->display();
    }
    
    //uploadfile
    private function getUploadFile() {
    $Uninum = time();
	    $filepath = $_SERVER['DOCUMENT_ROOT']."/Uploads/items/images/";//图片保存的路径目录
    	    if(!is_dir($filepath)){
    	    mkdir($filepath,0777, true);
    	}
     
    	$file_type = explode(".",$_POST['img_name']);
    	$filename = $Uninum.'.'.$file_type[1]; //生成文件名
    	move_uploaded_file($_FILES["my_img"]["tmp_name"],$filepath.$filename);
	    return '/Uploads/items/images/'.$filename;
    
    	}
    
    	//取得收藏
    private function getUserFavi() {
    		//$uid = $_SESSION['uid'];
    		$uid =$_SESSION['uid'];
    		$m=M();
    		$Sel_sql = "SELECT i.title, i.img,s.item_id FROM tp_item i, tp_shop_favi s ";
    		$Where_sql = "WHERE i.id = s.item_id and s.userid = ".$uid;
    		$result=$m->query($Sel_sql.$Where_sql);
    		return $result;
    		 
    }
    
    //取得选择收藏
    private function getSelFavi($result) {
    	$item_ids = "";
    	foreach($result as $val){
	    	if($_POST['txt_'.$val['item_id']] == "1"){
		    		$item_ids = $item_ids.$val['item_id'].",";
	   		 }
   		 }
    	$item_ids = substr($item_ids,0,strlen($item_ids)-1);
    	return $item_ids;
    }
    
    	//取得选择收藏
    	private function getMatchItem($item_ids) {
	    	$m=M();
	    	$Sel_sql = "SELECT * FROM tp_item ";
	    	$Where_sql = "WHERE id in ( ".$item_ids.")";
	    	$result=$m->query($Sel_sql.$Where_sql);
	    	return $result;
    	}  
    	
    	//积分使用说明
    	public function  jifenuse(){
    		$this->display();
    	}
    	
    	//个人资料说明
    	public function  singledata(){
    		$tokenTall = $this->getTokenTall();
    		$this->assign('tokenTall',$tokenTall);
    		$data['uid'] = session('uid');
    		if(!empty($data['uid'])){
    			$userinfo=M('user_info')->where($data)->find();
    			//$title_arr=array("晚装","正装","休闲","运动","打底");//
    			if(empty($userinfo)){
    				$this->assign('flag','0');//新增
    			}else{
    				$this->assign('flag','1');//编辑
    				$this->assign("uInfo",$userinfo);
    			}
    			//dump($userinfo);exit;
    			$this->display();
    		}else{
    			$this->error("服务器繁忙！");
    		}
    	}
    	
    	public function saveinfo(){
    		header("Content-type: text/html; charset=utf-8"); 
    		$data['uid'] = session('uid');
    		$flag=$this->_post('flag','trim');
    		if(!empty($data['uid'])){
	    		$data['sex'] = $this->_post("sex");
	    		$data['birthday']=$this->_post("birthday");
	    		$data['height']=$this->_post("height");
	    		$data['weight']=$this->_post("weight");
	    		$data['mail']=$this->_post("mail");
	    		$data['yifu_size']=$this->_post("yifu_size");
	    		$data['kuzi_size']=$this->_post("kuzi_size");
	    		$data['xie_size']=$this->_post("xie_size");
	    		
	    		$hobby_title_arr = $this->_post("hobby_title");
	    		$data['hobby_title'] = implode("|", $hobby_title_arr);
	    
	    		$hobby_color_arr = $this->_post("hobby_color");
	    		$data['hobby_color'] = implode("|", $hobby_color_arr);
	    		
	    		$hobby_style_arr = $this->_post("hobby_style");
	    		$data['hobby_style'] = implode("|", $hobby_style_arr);
	    		
	    		$hobby_element_arr = $this->_post("hobby_element");
	    		$data['hobby_element'] = implode("|", $hobby_element_arr);
	    		if($flag=='0'){//新增
	    			if(M('user_info')->add($data)){
	    				$this->success("保存成功",U("user/logintest"));
	    			}else{
	    				$this->error("保存失败");
	    			}
	    		}
	    		if($flag=='1'){//编辑
	    			if(M('user_info')->where(array('uid'=>$data['uid']))->save($data)){
	    				$this->success("保存成功",U("user/logintest"));
	    			}else{
	    				$this->error("保存失败");
	    			}
	    		}

    		}else{
    			$this->error("服务器繁忙！");
    		}	
    	}
}
