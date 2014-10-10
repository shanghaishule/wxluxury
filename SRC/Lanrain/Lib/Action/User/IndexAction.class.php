<?php
class IndexAction extends UserAction{
	//公众帐号列表
	public function index(){
		$where['uid']=session('uid');
		$group=D('User_group')->select();
		foreach($group as $key=>$val){
			$groups[$val['id']]['did']=$val['diynum'];
			$groups[$val['id']]['cid']=$val['connectnum'];
		}
		unset($group);
		
		$db=M('wxuser');
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('info',$info);
		$this->assign('group',$groups);
		$this->assign('page',$page->show());
		$this->display();
	}
	public function addressselect(){
		$upload_shop = M("upload_shop");
		$where["brand_name"] = $this->_get("name","trim");
		$where["status"] = array("eq","0");
		$result = $upload_shop->where($where)->select();
		if ($result){
			// 成功后返回客户端新增的用户ID，并返回提示信息和操作状态
			$this->ajaxReturn($result,"新增成功！",1);
		}else{
			// 错误后返回错误的操作状态和提示信息
			$this->ajaxReturn(0,"新增错误！",0);
		}
	}
	//添加公众帐号
	public function add(){
		$randLength=6;
		$chars='abcdefghijklmnopqrstuvwxyz';
		$where['uname']=session('uname');
		$len=strlen($chars);
		$randStr='';
		for ($i=0;$i<$randLength;$i++){
			$randStr.=$chars[rand(0,$len-1)];
		}
		$tokenvalue=$randStr.time();
		$this->assign('tokenvalue',$tokenvalue);
		$this->assign('email',time().'@yourdomain.com');
		
		//品牌
        $application= M("application")->where($where)->find();
        //dump($application);exit;
        $this->assign('mybrand',$application);
		$brand = M("brandlist")->select();
		$this->assign("brand",$brand);		
		
		//地址
		$addr_where['province'] = $application['province'];
		$addr_where['city'] = $application['city'];
		$addr_where['brand_name'] = $application['brand'];
		$addr_where['shop_name'] = $application['addr'];
		$addr = M('upload_shop')->where($addr_where)->find();
		//dump($addr);exit;
		$this->assign("myaddr",$addr);
		//公众号名称
		$this->assign('weixinName',$this->weixinName(15).rand(0,10000));
		//公众号
		$this->assign('weixinhao',$this->weixinNum(8).rand(0,100));
		//公众号原始id
		$this->assign('yid',$this->weixinNum(10).rand(0,1000));
		//地理信息
		//if (C('baidu_map_api')){
			//$locationInfo=json_decode(file_get_contents('http://api.map.baidu.com/location/ip?ip='.$_SERVER['REMOTE_ADDR'].'&coor=bd09ll&ak='.C('baidu_map_api')),1);
			//$this->assign('province',$locationInfo['content']['address_detail']['province']);
			//$this->assign('city',$locationInfo['content']['address_detail']['city']);
			//echo $_SERVER['REMOTE_ADDR'];var_dump($locationInfo);die();
		//}
	
		
		$this->display();
	}
	public function weixinNum($length){
		$str = null;
	   $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	   $max = strlen($strPol)-1;
	
	   for($i=0;$i<$length;$i++){
	    $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
	   }
	
	   return $str;
	}
	public function weixinName($length){
		$str = null;
		$strPol = "ABC-DEF_GHI_JKLMN_OPQRS_TUVWXY_Z01234-5678_9abc-defgh_ijk-lmn_opqrst-uvw-xyz";
		$max = strlen($strPol)-1;
	
		for($i=0;$i<$length;$i++){
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}
		return $str;
	}	
	public function edit(){
		$id=$this->_get('id','intval');
		$where['uid']=session('uid');
		
		$res=M('Wxuser')->where($where)->find($id);
		
		//店铺品牌
		$wecha_shop=M('wecha_shop');
		$brand_shop=M('brandlist');
		$data["tokenTall"] = $res["token"];
		$wedata = $wecha_shop->where($data)->find();
		$brand["id"]=$wedata["BelongBrand"];
		$this->assign("brand_shop",$brand_shop->where($brand)->find());
				
		
		$this->assign("wedata",$wedata);
		$this->assign('info',$res);
		$this->display();
	}
	
	public function del(){
		$where['id']=$this->_get('id','intval');
		$where['uid']=session('uid');
		if(D('Wxuser')->where($where)->delete()){
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}
	
	public function upsave(){
		$weChaShop = M("wecha_shop");
		$data1["address"] = $_POST["address"];
		/*
		$longitude = $this->_POST("longitude","trim");
		$longitudes = explode(",", $longitude);
		$data1["latitude"] = preg_replace('/\(/',"",$longitudes[0]);
		$data1["longitude"] = preg_replace('/\)/',"",$longitudes[1]);
		*/
		//更新地址
		$result = $this->getlatlng($data1["address"]);
		$data1["longitude"] = $result['long'];
		$data1["latitude"] = $result['lat'];
		
		$data1["shop_city"] = $this->_post("province","trim");
		$where_shop['weName']=$_POST["wxname"];
		$weChaShop->where($where_shop)->save($data1);
		$this->all_save('Wxuser');
	}	
	public function insert(){
		$data=M('User_group')->field('wechat_card_num')->where(array('id'=>session('gid')))->find();
		$users=M('Users')->field('wechat_card_num')->where(array('id'=>session('uid')))->find();
		/*if($users['wechat_card_num']<$data['wechat_card_num']){
			
		}else{
			$this->error('您的VIP等级所能创建的公众号数量已经到达上线，请购买后再创建',U('User/Index/index'));exit();
		}*/
		//$this->all_insert('Wxuser');
		//
		$db=D('Wxuser');
		if($db->create()===false){
			$this->error($db->getError());
		}else{
			//判断微信号是否已经开店
			$flag = false;
			$wecha_shop=M('wecha_shop');
			$haveuse["wxname"]=$_POST["wxname"];
			$olduser=$db->where($haveuse)->find();
			if ($olduser["wxuser"] != "") {
				$this->error('该微信号已经存在其他用户中，请选择其他公众号！',U('Index/index'));
			}else{				
			
				$id=$db->add();
				if($id){
					M('Users')->field('wechat_card_num')->where(array('id'=>session('uid')))->setInc('wechat_card_num');
					$this->addfc();
					$weChaShop = M("wecha_shop");
					
					$headurl = $_POST["headerpic"];
					$data1["headurl"] = substr($headurl, 0,strlen($headurl));
					$data1["weName"] = $_POST["wxname"];
					$data1["address"] = $_POST["address"];
					$up_shop['id'] = $this->_post("up_shop_id","trim");
					$select_shop = M("upload_shop")->where($up_shop)->find();
					
					$usergid=M("users")->where(array('id'=>session('uid')))->find();
					$data1['level']=$usergid['gid'];
					
					$data1["name"] = $select_shop["shop_name"];
					$data1["phone"] = $select_shop["phone"];
					/*
					$longitude = $this->_POST("longitude","trim");
					$longitudes = explode(",", $longitude);
					$data1["longitude"] = preg_replace('/\)/',"",$longitudes[1]);
					if ($data1["longitude"] == "") {
						$data1["longitude"] = $select_shop["longtitude"];
					}
					$data1["latitude"] = preg_replace('/\(/',"",$longitudes[0]);
					if ($data1["latitude"] == "") {
						$data1["latitude"] = $select_shop["lat"];
					}
					*/
					//更新地址
					$result = $this->getlatlng($data1["address"]);
					$data1["longitude"] = $result['long'];
					$data1["latitude"] = $result['lat'];
					
					
					$data1["HaveReal"] = 0;
					$data1["credit"] = 0;
					$data1["shop_city"] = $this->_post("province","trim");
					$data1["BelongBrand"] = $this->_POST("brandchoose","trim");
					$where_shop['weName']=$_POST["wxname"];	
					//将QQ号码传给用户店铺信息中
					$uid_1["id"] = $_SESSION['uid'];
					$user_in = M("users")->where($uid_1)->find();
					$data_M["uname"] = $user_in["username"];
					$application = M("application")->where($data_M)->find();
					if ($application) {
						$data1["qq"] = $data1["QQ"];
					}
					$Have_token = $wecha_shop->where($where_shop)->find();
					
					if ($Have_token['tokenTall'] != "") {
						$data1["tokenTall"] = $Have_token['tokenTall'];
						$tokenData["token"] = $Have_token['tokenTall'];
						$update_upload["tokenTall"] = $Have_token['tokenTall'];
						$flag = true;
						$where_shopw['wxname']=$_POST["wxname"];
						$db->where($where_shopw)->save($tokenData);
					}else{
						$data1["tokenTall"] = $_POST['token'];
						$update_upload["tokenTall"] = $_POST['token'];
						$weChaShop->add($data1);
					}
					
					//将上传的店铺状态设为已领取
					$update_upload["status"] = 1;
					$update_upload["longitude"] = $result['long'];
					$update_upload["lat"] = $result['lat'];
						
					M("upload_shop")->where($up_shop)->save($update_upload);
					
					if($flag){
						$this->success('欢迎回来',U('Index/index'));
					}else{
						$this->success('操作成功',U('Index/index'));
					}
					
				}else{
					$this->error('操作失败',U('Index/index'));
				}
			}
		}
		
	}
	public function getlatlng($address){
		$info3 = array();
		$request = $this->curlGet('http://api.map.baidu.com/geocoder/v2/?address='.rtrim($address).'&output=json&ak=1a555421447b51e2fbe7317a2656bc92');
		$requestArray = json_decode($request, true);
		//dump($requestArray);exit;
		if ($requestArray['status']==0) {
			$info3['lat'] = $requestArray['result']['location']['lat'];
			$info3['long'] = $requestArray['result']['location']['lng'];
		}else{
			$info3['lat'] = 0;
			$info3['long'] = 0;
		}
		//dump($info3);exit;
			
		return $info3;
	
	}
	public function curlGet($url){
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
	
	
	//功能
	public function autos(){
		$this->display();
	}
	
	public function addfc(){
		$token_open=M('Token_open');
		$open['uid']=session('uid');
		//判断微信号是否已经开店
		$wecha_shop=M('wecha_shop');
		$where_shop['weName']=$_POST["wxname"];
		$Have_token = $wecha_shop->where($where_shop)->find();
		if ($Have_token['tokenTall'] != "") {
			$open['token'] = $Have_token['tokenTall'];
		}else{
			$open['token']=$_POST['token'];
		}
		
		$gid=session('gid');
		$fun=M('Function')->field('funname,gid,isserve')->where('`gid` <= '.$gid)->select();
		foreach($fun as $key=>$vo){
			$queryname.=$vo['funname'].',';
		}
		$open['queryname']=rtrim($queryname,',');
		$token_open->data($open)->add();
	}
	public function get_goods(){
		$token = $this->_get("token","trim");
		
		//实体店信息
		$wecha_shop = M("wecha_shop");
	    //实体商品
		$items = M("item");
		//店铺从天猫拿下来的商品
		$items_taobao = M("item_taobao");

		$wecha_shop_data["tokenTall"] = $token;
		$items_having["tokenTall"] = $token;
		$get_num = 0; //领取成功计数器
		$failed_num = 0;//领取失败计数器
		$having_num = 0; //已经领取计数器
		
		$brand = $wecha_shop->where($wecha_shop_data)->find();
		$items_taobao_data["brand"] = $brand["BelongBrand"];
		$items_taobao_data["Huohao"] = $this->_post("Huohao","trim");
		$item_goods = $items_taobao->where($items_taobao_data)->select();
		$item_new = array();
		foreach ($item_goods as $item_good){
			$item_new = $item_good;
			$item_new["tokenTall"] = $token;
			$item_new["id"]="";
			$item_new["old_price"] = $item_good["price"];
			$items_having["Uninum"] = $item_good["Uninum"];
			$items_having["tokenTall"] = $token;
			//var_dump($item_new);die();
			if ($items->where($items_having)->find()) {
				$having_num ++;
			}
			elseif ($items->add($item_new)) {
				$get_num ++;
			}else{
				$failed_num ++;
			}
		}
		if ($get_num > 0) {
			$message = "您本次成功领取".$get_num."商品，有".$failed_num ++."商品没有成功";
		}else{
			$message = "无法入库，你填写的货号不存在或者该商品已经存在您的库中！";
		}
		$this->success($message,U('Index/index'));
	}
	public function usersave(){
		$pwd=$this->_post('password');
		if($pwd!=false){
			
			$password["password"]=$pwd;
			$where2['id']=$_SESSION['uid'];
			$user = M('Users')->where($where2)->find();
			$applicant["uname"] = $user["username"];
			M("application")->where($applicant)->save($password);
			
			$data['password']=md5($pwd);
			$data['id']=$_SESSION['uid'];
			if(M('Users')->save($data)){
				$this->success('密码修改成功！',U('Index/index'));
			}else{
				$this->error('密码修改失败！',U('Index/index'));
			}
		}else{
			$this->error('密码不能为空!',U('Index/useredit'));
		}
	}
	public function userpic(){
		if(IS_POST){
			$picurl=$this->_post('picurl');
			if($picurl!=false){
				$data['headerpic']=$picurl;
				$data['id']=$_SESSION['uid'];
				if(M('Users')->save($data)){
					$this->success('头像修改成功！',U('Index/index'));
				}else{
					$this->error('头像修改失败！',U('Index/userpic'));
				}
			}else{
				$this->error('头像不能为空!',U('Index/userpic'));
			}
		}else{
			$this->display();
		}
	}
	//处理关键词
	public function handleKeywords(){
		$Model = new Model();
		//检查system表是否存在
		$keyword_db=M('Keyword');
		$count = $keyword_db->where('pid>0')->count();
		//
		$i=intval($_GET['i']);
		//
		if ($i<$count){
			$img_db=M($data['module']);
			$back=$img_db->field('id,text,pic,url,title')->limit(9)->order('id desc')->where($like)->select();
			//
			$rt=$Model->query("CREATE TABLE IF NOT EXISTS `tp_system_info` (`lastsqlupdate` INT( 10 ) NOT NULL ,`version` VARCHAR( 10 ) NOT NULL) ENGINE = MYISAM CHARACTER SET utf8");
			$this->success('关键词处理中:'.$row['des'],'?g=User&m=Create&a=index');
		}else {
			exit('更新完成，请测试关键词回复');
		}
	}
}
?>