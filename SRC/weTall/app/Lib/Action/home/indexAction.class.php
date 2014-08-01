<?php
class indexAction extends frontendAction {

    public function index() {
    	//取商家token值，取不到则默认为空
    	$tokenTall = $this->getTokenTall();
    	$_SESSION["tokenTall"]=$tokenTall;
    
    	//判断是微信的环境
    	$systemBrowse="X";
    	$agent = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($agent,"icroMessenger")) {
    		$systemBrowse="Y";
    	}
    	
    	/*****首页广告***/
    	$ad= M('ad');
    	$where = array('board_id'=>1, 'status'=>1, 'tokenTall'=>$tokenTall);
    	$ads = $ad->field('url,content,desc')->where($where)->limit(5)->order('ordid asc')->select();
        $this->assign('ad',$ads);
        
        /*****首页广告end******/
        
        /***商品分类**/
        $item_cate=M("item_cate")->select();
        $this->assign('item_cate',$item_cate);
        /***商品分类end***/
        
        /****最新商品*****/
        $where = array('tokenTall'=>$tokenTall);
        $news = $this->getItem_cate($where);
        /****最新商品 END*****/
         
        /****推荐商品*****
        $where = array('tuijian'=>1, 'tokenTall'=>$tokenTall);
        $tuijian = $this->getItem($where);
        /****推荐商品 END*****/
        
        $brand = M("brandlist")->select();
        $this->assign("brand",$brand);
        
        /*店铺信息*/
        $weChaShop = M("wecha_shop");
        if($tokenTall == ""){
          $weshopData["tokenTall"] = $_SESSION["tokenTall"];
        }else{
        	$weshopData["tokenTall"] = $tokenTall;
        }
        $weChaShopDetail = $weChaShop->where($weshopData)->find();//var_dump($weshopData);die();
        $this->assign("weshopData",$weChaShopDetail);
        
        /*收藏
        if ($_SESSION['user_info']) {
        	$userid = $_SESSION['user_info']['id'];
        	$shopfav_mod = M('shop_favi');
        	$wheredata = array('userid'=>$userid, 'tokenTall'=>$tokenTall);
        	if ($shopfav_mod->where($wheredata)->find()) {
        		$favi = "yes";
        	}else{
        		$favi = "no";
        	}
        }else{
        	$favi = "no";
        }
        */
        //首次进入首页
        $index_num2 = $_SESSION["index_num2"];
        if ($index_num2 == "") {
        	$index_num2 = 0;
        }
        $index_num2 = $index_num2 + 1;
        $_SESSION["index_num2"] = $index_num2;
        
        $this->assign('favi',$favi);
        $this->assign("systemBrowse",$systemBrowse);
        $this->assign("index_num2",$_SESSION["index_num2"]);
        $this->assign('news',$news);
        $this->assign('tuijian',$tuijian);
        $this->_config_seo();
        $this->display();
    }
    public function brandselect(){
    	$upload_shop = M("aused_taobao");
    	$brand_name = $_GET["brand_name"];
    
    	$where["name"] = $brand_name;
    	$brand_id = M("brandlist")->where($where)->find();
    	
    	$where2["brand_id"] = $brand_id["id"];
    	$result2 = $upload_shop->where($where2)->find();
    
    	$result = $result2["url"];
    
    	if ($result){
    		// 成功后返回客户端新增的用户ID，并返回提示信息和操作状态
    		$this->ajaxReturn($result,"新增成功！",1);
    	}else{
    		// 错误后返回错误的操作状态和提示信息
    		$this->ajaxReturn(0,"新增错误！",0);
    	}
    }
    public function saohuo(){
    	$brand_id=$this->_get("brand_id","trim");
    	$id=$this->_get("id","intval");
    	$set_discount=M("set_discount");
    	$discount["id"]=$id;
    	$_SESSION["huodong_id"]=$id;
    	$data_act=$set_discount->where($discount)->find();
    	$start_time=strtotime($data_act["date"]. $data_act["start_time"]);
    	$end_time=strtotime($data_act["date"]. $data_act["end_time"]);
    	
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	//$start_time=strtotime(date("2014-04-20 20:54:00",time()));
    	$nowTime = time();
    	
    	if ($nowTime < $end_time and $nowTime > $start_time) {
    			$where["goods_stock"] = array("neq",0);   
    			$where["price"] = array(array("neq",0),array("neq",1));
    			if ($_SESSION["item_data"] == "") {   			
	    			$item_data=M("item")->query("select * from tp_item T1,
					(select `goods_stock`,`Uninum`, Min(`price`) as mprice, Min(`id`) as mid  from `tp_item`
					group by `Uninum`
					having 
					`goods_stock` <> 0
					) T2
					where T1.`id` = T2.`mid` order by `buy_num` limit 400");
	    			$_SESSION["item_data"]=$item_data;
    			}else{
    				$item_data = $_SESSION["item_data"];
    			}
    			//var_dump($item_data);die();
    			$item_goods=array();
    			$i=0;
    			$num_now = $nowTime-$start_time;
    			foreach ($item_data as $var_item){   				
    				$item_goods[] = $var_item;
    				$where["Uninum"] = $var_item["Uninum"];
    				$item_tao = M("item_taobao")->where($where)->find();
    				$item_goods[$i]["zhekou"]=round($var_item["price"]*10/$item_tao["price"],1);
    				$i++;
    				if ($num_now <= $i){break;}
    			}
    			
    			$reverse_goods=array_reverse($item_goods);
    			$this->assign("item",$reverse_goods);
    			$this->display();
    	}elseif($nowTime < $start_time){
    			echo "还未开始";die();
    	}else{
    			echo "活动已经结束";die();
    	}
    	
    }
    public function check_huodong(){
    	$set_discount=M("set_discount");
    	$discount["id"]=$_SESSION["huodong_id"];
    	$data_act=$set_discount->where($discount)->find();
    	$start_time=strtotime($data_act["date"]. $data_act["start_time"]);
    	$end_time=strtotime($data_act["date"]. $data_act["end_time"]);
    	 
    	$start_time=strtotime(date("2014-04-20 20:54:00",time()));
    	$nowTime = time();
    	 
    	if ($nowTime < $end_time and $nowTime > $start_time) {   		
    		$this->ajaxReturn(1,"可以购买！",1);
    	}else{
    		$where["status"] = 2;
    		$set_discount->where($discount)->save($where);
    		$this->ajaxReturn(0,"购买错误！",0);
    	}
    }
    public function matchtest(){
    	$redirecturl = urlencode("http://www.kuyimap.com/weTall/index.php?g=home&m=index&a=match");
    	$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3079f89b18863917&redirect_uri=".$redirecturl."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
    	//$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3079f89b18863917&redirect_uri=".$redirecturl."&response_type=code&scope=snsapi_userinfo&state=zcb#wechat_redirect";
    	header("Location: ".$url);
    }
    public function match() {
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
    	$m=M();
    	$Sel_sql = "SELECT * from tp_match where is_send = 1 order by create_time desc" ;
    	$result=$m->query($Sel_sql);
    	$item_favi_detail = M("item");
    	$match_table = array();
    	$id=0;
    
    	$match_favi = array();
    	foreach ($result as $match_result){
    		$match_table[] = $match_result;
    		$match_table[$id]['create_time']=fdate($match_result['create_time']);
    		$username = M("user")->where("id=".$match_result["uid"])->find();
    		//总评数
    		$sum_com=M("match_comments")->where("match_id=".$match_result["id"])->count();
    		//总赞数
    		$sum_love=M("match_love")->where("matchid=".$match_result["id"])->count();
    		
    		$match_table[$id]["uname"] = $username["nickname"];
    		$match_table[$id]["userimgurl"] = $username["headimgurl"];
    		$match_table[$id]['sum_c']=$sum_com;
    		$match_table[$id]['sum_l']=$sum_love;
    		$id ++;
    		if ($match_result != "" or $match_result != null) {
    			$item_favi = explode(",", $match_result["item_ids"]);
    			foreach ($item_favi as $val){
    				$match_favi_sequence["id"] = $match_result["id"];
    				$item = $item_favi_detail->where("id=".$val)->find();
    				$match_favi_sequence["favi_name"] = $item["title"];
    				$match_favi_sequence["favi_img"] = $item["img"];
    				$match_favi_sequence["favi_price"] = $item["price"];
    				$match_favi_sequence["item_id"]=$val;
    				$match_favi[] = $match_favi_sequence;
    			}
    		}
    	}
    	//var_dump($match_table);die();
    	$this->assign("match_table",$match_table);
    	$this->assign("favi_table",$match_favi);
    	$this->display();
    }
    

    //搭配按热度
    public function matchHot(){
    	$m=M();
    	$Sel_sql = "SELECT * from tp_match where is_send = 1 order by create_time desc" ;
    	$result=$m->query($Sel_sql);
    	$item_favi_detail = M("item");
    	$match_table = array();
    	$id=0;
    	
    	$match_favi = array();
    	foreach ($result as $match_result){
    		$match_table[] = $match_result;
    		$match_table[$id]['create_time']=fdate($match_result['create_time']);
    		$username = M("user")->where("id=".$match_result["uid"])->find();
    		//总评数
    		$sum_com=M("match_comments")->where("match_id=".$match_result["id"])->count();
    		//总赞数
    		$sum_love=M("match_love")->where("matchid=".$match_result["id"])->count();
    	
    		$match_table[$id]["uname"] = $username["nickname"];
    		$match_table[$id]["userimgurl"] = $username["headimgurl"];
    		$match_table[$id]['sum_c']=$sum_com;
    		$match_table[$id]['sum_l']=$sum_love;
    		$id ++;
    		if ($match_result != "" or $match_result != null) {
    			$item_favi = explode(",", $match_result["item_ids"]);
    			foreach ($item_favi as $val){
    				$match_favi_sequence["id"] = $match_result["id"];
    				$item = $item_favi_detail->where("id=".$val)->find();
    				$match_favi_sequence["favi_name"] = $item["title"];
    				$match_favi_sequence["favi_img"] = $item["img"];
    				$match_favi_sequence["favi_price"] = $item["price"];
    				$match_favi_sequence["item_id"]=$val;
    				$match_favi[] = $match_favi_sequence;
    			}
    		}
    	}
    	$match_table=$this->array_sort($match_table,'sum_c','desc');
    	//dump($match_table);die();
    	$this->assign("match_table",$match_table);
    	$this->assign("favi_table",$match_favi);
    	$this->display('match');
    }
    
    //搭配按时间
    public function matchTime(){
    	$m=M();
    	$Sel_sql = "SELECT * from tp_match where is_send = 1 order by create_time desc" ;
    	$result=$m->query($Sel_sql);
    	$item_favi_detail = M("item");
    	$match_table = array();
    	$id=0;
    	
    	$match_favi = array();
    	foreach ($result as $match_result){
    		$match_table[] = $match_result;
    		$match_table[$id]['create_time']=fdate($match_result['create_time']);
    		$match_table[$id]['add_time']=$match_result['create_time'];
    		$username = M("user")->where("id=".$match_result["uid"])->find();
    		//总评数
    		$sum_com=M("match_comments")->where("match_id=".$match_result["id"])->count();
    		//总赞数
    		$sum_love=M("match_love")->where("matchid=".$match_result["id"])->count();
    	
    		$match_table[$id]["uname"] = $username["nickname"];
    		$match_table[$id]["userimgurl"] = $username["headimgurl"];
    		$match_table[$id]['sum_c']=$sum_com;
    		$match_table[$id]['sum_l']=$sum_love;
    		$id ++;
    		if ($match_result != "" or $match_result != null) {
    			$item_favi = explode(",", $match_result["item_ids"]);
    			foreach ($item_favi as $val){
    				$match_favi_sequence["id"] = $match_result["id"];
    				$item = $item_favi_detail->where("id=".$val)->find();
    				$match_favi_sequence["favi_name"] = $item["title"];
    				$match_favi_sequence["favi_img"] = $item["img"];
    				$match_favi_sequence["favi_price"] = $item["price"];
    				$match_favi_sequence["item_id"]=$val;
    				$match_favi[] = $match_favi_sequence;
    			}
    		}
    	}
    	$match_table=$this->array_sort($match_table,'create_time','desc');
    	//dump($match_table);die();
    	$this->assign("match_table",$match_table);
    	$this->assign("favi_table",$match_favi);
    	$this->display('match');
    }
        
    public function addressselect(){
    	$upload_shop = M("item");
    	$color = $_GET["color"];
    	$size = $_GET["size"];
    	
    	$where["id"] = $_GET["item_id"];
    	$result2 = $upload_shop->where($where)->find();
        $detail_stock=explode(",", $result2["detail_stock"]);
        foreach ($detail_stock as $stock){
        	$stock_real=explode("|",$stock);
        	if ($stock_real[0] == $color and $stock_real[1] == $size) {
        		$item_stcok = $stock_real[2];
        	}
        }
        
        $result = $item_stcok;
        
    	if ($result){
    		// 成功后返回客户端新增的用户ID，并返回提示信息和操作状态
    		$this->ajaxReturn($result,"新增成功！",1);
    	}else{
    		// 错误后返回错误的操作状态和提示信息
    		$this->ajaxReturn(0,"新增错误！",0);
    	}
    }
    public function intime() {
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	$discount_shop = M("set_discount");
    	$brand = M("brandlist");
    	$set_discount=M("set_discount");
    	$discount_data = $discount_shop->order("status desc,date asc,start_time asc")->select();
    	
    	$nowTime = time();
    	foreach ($discount_data as $val_data){
    		$start_time=strtotime($val_data["date"]. $val_data["start_time"]);
    		$end_time=strtotime($val_data["date"]. $val_data["end_time"]);
    		$discount[id]=$val_data["id"];

    		if($nowTime < $start_time){
	    		$update_status3["status"] = "1";
	    		$set_discount->where($discount)->save($update_status3);
    		}elseif($nowTime > $end_time){
    			$update_status2["status"] = "0";
    			$set_discount->where($discount)->save($update_status2);
    		}else{
    			$update_status["status"] = "2";
    			$set_discount->where($discount)->save($update_status);
    		}
    	}
    	//var_dump($discount_data);die();
    	$this->assign("huodongstatus",$update_status["status"]);
    	$this->assign("brand",$brand->select());
    	$this->assign("ontime",$discount_data);
    	$this->display();
    }
    public function discount(){
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	$discount_shop = M("discount_shop");
    	$data = $discount_shop->select();
    	
    	$this->assign("discount_shop",$data);
    	$this->assign("City","找品牌");
    	$this->display();
    }
    public function Oneyuan(){
    	$tokenTall = $this->getTokenTall();
    	$_SESSION["tokenTall"]=$tokenTall;
    	
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	 
    	//判断是微信的环境
    	$systemBrowse="X";
    	$agent = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($agent,"icroMessenger")) {
    		$systemBrowse="Y";
    	}
    	 
	    	/*****首页广告***/
	    	$ad= M('ad');
	    	$where = array('board_id'=>1, 'status'=>1, 'tokenTall'=>$tokenTall);
	    	$ads = $ad->field('url,content,desc')->where($where)->limit(5)->order('ordid asc')->select();
	    	$this->assign('ad',$ads);
    	/*****首页广告end******/
    	  
    	/****最新商品*****/
    	$where = array("Oneyuan"=>1);
    	$news = $this->getItem_cate($where);
    	/****最新商品 END*****/
    	  
    	/****推荐商品*****
    	 $where = array('tuijian'=>1, 'tokenTall'=>$tokenTall);
    	$tuijian = $this->getItem($where);
    	/****推荐商品 END*****/
    	
    	$brand = M("brandlist")->select();
    	$this->assign("brand",$brand);
    	
    	/*店铺信息*/
    	$weChaShop = M("wecha_shop");
    	if($tokenTall == ""){
    		$weshopData["tokenTall"] = $_SESSION["tokenTall"];
    	}else{
    		$weshopData["tokenTall"] = $tokenTall;
    	}
    	$weChaShopDetail = $weChaShop->where($weshopData)->find();//var_dump($weshopData);die();
    	$this->assign("weshopData",$weChaShopDetail);
    	
    	/*收藏*/
    	if ($_SESSION['user_info']) {
    		$userid = $_SESSION['user_info']['id'];
    		$shopfav_mod = M('shop_favi');
    		$wheredata = array('userid'=>$userid, 'tokenTall'=>$tokenTall);
    		if ($shopfav_mod->where($wheredata)->find()) {
    			$favi = "yes";
    		}else{
    			$favi = "no";
    		}
    	}else{
    		$favi = "no";
    	}
    	
    	//首次进入首页
    	$index_num2 = $_SESSION["index_num2"];
    	if ($index_num2 == "") {
    		$index_num2 = 0;
    	}
    	$index_num2 = $index_num2 + 1;
    	$_SESSION["index_num2"] = $index_num2;
    	
    	$this->assign('favi',$favi);
    	$this->assign("systemBrowse",$systemBrowse);
    	$this->assign("index_num2",$_SESSION["index_num2"]);
    	$this->assign('news',$news);
    	$this->assign('tuijian',$tuijian);
    	$this->_config_seo();
    	$this->display();
    }
    public function navigate(){
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	$start_point_lat = $this->_get("start_point_lat","trim");
    	$start_point_lng = $this->_get("start_point_lng","trim");
    	$end_point_lat = $this->_get("end_point_lat","trim");
    	$end_point_lng = $this->_get("end_point_lng","trim");
    	$shop_id["tokenTall"] = $this->_get("shop","trim");
    	$display_mode = $this->_get("dmodel","trim");
    	$token = $this->_get("tokenTall","trim");
    	if ($token != "") {
    		$shop_id2["tokenTall"] = $token;
    		$shop_data = M("wecha_shop")->where($shop_id2)->find();
    	}else{
    		$shop_data = M("wecha_shop")->where($shop_id)->find();
    	}
    	
    	$this->assign("dmodel",$display_mode);
    	$this->assign("shopinfo",$shop_data);
    	$this->assign("start_point_lat",$start_point_lat);
    	$this->assign("start_point_lng",$start_point_lng);
    	$this->assign("end_point_lat",$end_point_lat);
    	$this->assign("end_point_lng",$end_point_lng);
    	$this->display();
    }
    public function get_loaction(){
    	$ip = get_client_ip();
    	$url = "http://api.map.baidu.com/location/ip?ak=omi69HPHpl5luMtrjFzXn9df&ip=$ip&coor=bd09ll";
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$output = curl_exec($ch);
    	if(curl_errno($ch))
    	{ echo 'CURL ERROR Code: '.curl_errno($ch).', reason: '.curl_error($ch);}
    	curl_close($ch);
    	$info = json_decode($output, true);
    	if($info['status'] == "0"){
    		$lotx = $info['content']['point']['y'];
    		$loty = $info['content']['point']['x'];
    		$citytemp = $info['content']['address_detail']['city'];
    		$keywords = explode("市",$citytemp);
    		$city = $keywords[0];
    	}
    	else{
    		$lotx = "34.2597";
    		$loty = "108.9471";
    		$city = "西安";
    	}
    	if ($lotx != "") {
    		$_SESSION["longtitude"] = $loty;
    		$_SESSION["latitude"] = $lotx;
    	} 
    }
    
    public function test(){  
    	//$this->get_loaction();	
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	if (IS_POST) {
    		$wecha_shop = M("upload_shop");
    		$longitude = $this->_POST("longitude","trim");
    		$latitude = $this->_POST("latitude","trim");
    		if ($latitude == "") {
    			$longitude = $_SESSION["longtitude"] ;
	    		$latitude = $_SESSION["latitude"] ;
    		}else{
	    		$_SESSION["longtitude"] = $longitude;
	    		$_SESSION["latitude"] = $latitude;
    		}
    		$brand_id = $this->_POST("brand_id","trim");
    		$brand_data['id'] = $brand_id;
    		$brandval = M("brandlist");
    		$volumn = $brandval->where($brand_data)->find();
    		$brand_data_new["volume"] = $volumn["volume"] + 1;
    		$brandval->where($brand_data)->save($brand_data_new);
    		
    		$where["brand_name"] = $volumn["name"];
    		$data["id"]=$brand_id;
    		$endPoint = $wecha_shop->where($where)->select();
    		$nearShop=array();
    		if ($longitude != "" and $latitude != "") {
	    		foreach ($endPoint as $end){    
	    			if ($end["lbs_addr"] != "" and $end["longtitude"] != "") {		
	    				$end["nearJuli"] = $this->GetDistance($latitude,$longitude,$end["lat"],$end["longtitude"]);
	    				//echo $end["nearJuli"]."--0".$latitude."----1<br>".$longitude."----2<br>".$end["lat"]."----3<br>".$end["longtitude"]."===".$end["nearJuli"];die();
	    				$nearShop[] = $end;
	    			}
	    		}
    		}
    		
    		$start_point_lat = $latitude;
    		$start_point_lng = $longitude;
    		
    		//排序
    		$new_nearShop = $this->array_sort($nearShop,"nearJuli", "asc");
    		$length = count($nearShop);
    		if ($length > 10) {
    			$length = 10;
    		}
    		
    	    $brand_ar = M("brandlist")->where($data)->find();
    		
    		$this->assign("brand",$brand_ar);
    		$this->assign("title",$brand_ar["name"]);
    		$this->assign("countShop",$length);
    		$this->assign("start_point_lat",$start_point_lat);
    		$this->assign("start_point_lng",$start_point_lng);
    		$this->assign("searchNear","Y");
    		$this->assign("nearShop",$new_nearShop); 
    		//dump($new_nearShop);exit;
    	}
    	$url = "http://api.map.baidu.com/geocoder?location=".$latitude.",".$longitude."&output=xml&key=28bcdd84fae25699606ffad27f8da77b";
    	//$url = "http://api.map.baidu.com/geocoder?location=31.256748,121.595578&output=xml&key=28bcdd84fae25699606ffad27f8da77b";
    	$city_data = file_get_contents($url);
    	preg_match('/<city>.*<\/city>/',$city_data,$total_page);
    	//$city_info = iconv('GBK', 'UTF-8',$total_page[0]);echo $city_info;die();
    	$currentcity = preg_replace('/市/',"",$total_page[0]);
    	$this->assign("City",$currentcity);
    	
    	$this->display();
    }
    /*
     * 排序
     */
    public function array_sort($arr,$keys,$type='asc'){
    	$keysvalue = $new_array = array();
    	foreach ($arr as $k=>$v){
    		$keysvalue[$k] = $v[$keys];
    	}
    	if($type == 'asc'){
    		asort($keysvalue);
    	}else{
    		arsort($keysvalue);
    	}
    	reset($keysvalue);
    	foreach ($keysvalue as $k=>$v){
    		$new_array[$k] = $arr[$k];
    	}
    	return $new_array;
    }
    /* 用来计算任意两点经纬度的距离
     * $len_type=1代表计算结果单位是米  $len_type>1代表是公里
     * $decimal代表计算结果的小数点位数
     */
    public function GetDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
    		$theta = $longitude1 - $longitude2;
    		$miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
    		$miles = acos($miles);
    		$miles = rad2deg($miles);
    		$miles = $miles * 60 * 1.1515;
    		$feet = $miles * 5280;
    		$yards = $feet / 3;
    		$kilometers = $miles * 1.609344;
    		$meters = $kilometers * 1000;
    		$kilometers = round($kilometers*1.1,2);
    		return $kilometers;
    		//return compact('miles','feet','yards','kilometers','meters');

	}
    public function getItem_cate($where = array())
    {
    	$where_init = array('status'=>'1');
    	$where =array_merge($where_init, $where); 
    
    	return $item=M('item')->where($where)->select();
    }
   
    public function search(){
    	//$this->get_loaction();
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	//所有的搜索进入比价页面
    	$this->assign("compare","Y");
    	
    	$brand = M("brandlist")->select();
    	$this->assign("brand",$brand);
    	
    	$_SESSION["search_all"]="N";
    	if($this->_get("search_all","trim") != ""){
    		$_SESSION["search_all"]="Y";
    		$this->assign("searchall","Y");
    	}
    	
    	//排序字段和方式的获得
    	$sortByStr=$this->_get("sortid","trim");
    	$sortmethod=$this->_get("sortmethod","trim");
    	if($sortByStr == "" or $sortmethod == ""){
    		$sortBy = "add_time desc";
    		$sortByStr="add_time";
    		$sortmethod="desc";
    	}else{
    		
	    	if ($_SESSION["sortstr"] == $sortByStr) {    			
		    	if($sortmethod == "asc"){
		    		$sortmethod="desc";
		    	}else{
		    		$sortmethod="asc";
		    	}
	    	}else{
	    		$sortmethod="desc";
	    	}
	    	$sortBy = $sortByStr." ".$sortmethod;
	    }
    	$this->assign("sortfield",$sortByStr);
    	$_SESSION["sortstr"]=$sortByStr;
    	$this->assign("sortmethod",$sortmethod);
    	if(IS_POST){
    	 //搜索关键字时候	
    		$keyword=$this->_post("txtkeyword","trim");
    		$brandid=$this->_post("brand_id","trim");
    		if ($keyword != "") {
    			$this->assign("title","查询结果");
    			$this->assign("City","附近店铺");
    			$this->assign("gohref","Y");
    		}
    		
    		//搜索的方式本店，微服客，店铺
    		$method=$this->_post("method");
    		
    		$tokenTall = $this->getTokenTall();
    		if($tokenTall != ""){
    			$token= $tokenTall;
    		}else{
    			$token=$_SESSION["tokenTall"];
    		}
    		$this->assign("method",$method);
    		if ($brandid != "") {
    			$latitude = $this->_post("latitude");
    			$longitude = $this->_post("longitude");
    			if ($latitude != "" and $longitude != "") {
    				$_SESSION["latitude"] = $latitude;
    				$_SESSION["longtitude"] = $longitude;
    			}
    			
    			$brand_name = M("brandlist")->where("id=".$brandid)->find();
    			$this->assign("title",$brand_name["name"]);
    			$this->assign("City","附近店铺");
    			$this->assign("gosearch","Y");
    			
    			$this->assign("longitude",$_SESSION["longtitude"]);
    			$this->assign("latitude",$_SESSION["latitude"]);
    			
    			$this->assign("brandid",$brandid);
    			$this->nextPageBrand($_SESSION['token'],$brandid,$itemid="",$sortBy);
    		}
    		elseif($keyword == ""){
    			$this->error("请输入关键字！");
    		}
    		else if($method=="local"){//本店
    			$this->nextPage($method, $keyword,$sortBy, $token);
                $_SESSION['keyword']=$keyword;
                $_SESSION['token']=$token;
                $_SESSION['method']=$method;
    		}else if($method=="weFig"){//微服客
    			$this->nextPage($method, $keyword,$sortBy);
                $_SESSION['keyword']=$keyword;
                $_SESSION['method']=$method;
    		}else{//店铺内搜索微服客
    			$this->nextPage($method, $keyword,$sortBy);
    			$_SESSION['keyword']=$keyword;
    			$_SESSION['method']=$method;
    		}
    		
    	}else{
    		$itemid=$this->_get("itemid","trim");
    		$brandid=$this->_get("brandid","trim");
    		$method2=$this->_get("method","trim");
    		if($method2 != "local" and $method2 != "weFig" and $method2 != "shop" and $method2 != ""){//类别搜索
    			$this->assign("method",$method2);
    			$this->nextPagetuan($_SESSION['token'],$method2,$sortBy);
    		
    		}else if ($brandid != ""){//品牌
    			//$this->assign("method",$brandid);
    			$this->assign("brandid",$brandid);
    			$this->nextPageBrand($_SESSION['token'],$brandid,$itemid,$sortBy);
    		}else if ($itemid != "") {//新品上市  服装鞋帽等
    			//$this->assign("method",$itemid);
    			$this->assign("itemid",$itemid);
    			$this->assign("title",$this->_get("itemname","trim"));
    			$this->nextPageCate($_SESSION['token'],$itemid,$sortBy);
    		}else if($_SESSION['method'] == "local"){//本店搜索
    			$this->assign("method",$_SESSION['method']); 
    		    $this->nextPage($_SESSION['method'], $_SESSION['keyword'],$sortBy, $_SESSION['token']);
    		}else{//关键字搜索后的分页
    			$this->assign("method",$_SESSION['method']);
    			
    			$this->nextPage($_SESSION['method'], $_SESSION['keyword'],$sortBy);
    		}
    	}
    	
    }
    public function nextPagetuan($token,$itemid,$sortBy){
    	$tokenTall = $token;
    	$this->assign('tokenTall',$tokenTall);
    	
    	switch ($itemid) {
    		case "new": $method="0";break;
    		case "recom":$method="1";break;
    		case "free":$itemCate="餐饮娱乐";break;
    		case "fuzhuang":$itemCate="服装鞋帽";break;
    		case "shuma":$itemCate="手机数码";break;
    		case "shenghuo":$itemCate="家用电器";break;
    		case "tushu":$itemCate="母婴用品";break;
    		case "huazhuang":$itemCate="美妆饰品";break;
    		case "meishi":$itemCate="百货食品";break;
    	}
    	
    	$item = M("item_taobao");
    	if($itemCate == ""){
    		if ($method=="0") {
    			$condition["news"] = "1";
    		}else{
    		    $condition["tuijian"] = $method;
    		}
    	}else{
    		$name["name"]=$itemCate;
    		$item_cate=M("item_cate")->where($name)->select();
    		foreach ($item_cate as $val){
    			$data["pid"]=$val["id"];
    			$itemID=M("item_cate")->where($data)->select();
    		}
    		foreach ($itemID as $varL){
    			$condition2[]=$varL["id"];
    		}
    		$condition["cate_id"]=array('in',$condition2);
    	}    	
    
    	if(count($condition2) != 0 or $method != ""){
    		$count = $item->where($condition)->count();   	
	    	$Page       = new Page($count,$count);// 实例化分页类 传入总记录数
	    	// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
	    	$nowPage = isset($_GET['p'])?$_GET['p']:1;
	    	$show       = $Page->show();// 分页显示输出
	    	$carryrecord  = $item->where($condition)->order($sortBy)->limit($Page->firstRow.','.$Page->listRows)->select();
    	}
	    	//var_dump($carryrecord);die();
    	$this->assign("item",$carryrecord);
    	$this->assign("itemcate","Y");
    	$this->assign('page',$show);// 赋值分页输出pti
    	$this->assign("count",$count);
    	$this->display();
    }
    public function compare(){
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	
    	$Huohao["Huohao"] = $this->_get("Huohao","trim");
    	//$item_huohao = M("item")->where($Huohao)->select();
    	$item_taobao = M("item_taobao")->where($Huohao)->find();
    	$brand["id"] = $item_taobao["brand"];
    	$brand_data = M("brandlist")->where($brand)->find();
    	
    	$brand2["BelongBrand"] = $item_taobao["brand"];
    	//$wecha_shop = M("wecha_shop")->where($brand2)->select();
        
    	$result = M()->table(array("tp_item"=>"i","tp_wecha_shop"=>"w"))->field("i.id,i.price,w.name,w.level")->where("i.tokenTall=w.tokenTall AND i.Huohao='".$Huohao["Huohao"]."' AND w.BelongBrand='".$brand2["BelongBrand"]."'")->order("w.level asc")->select();
    	//dump($result);exit;
    	//$this->assign("item",$item_huohao);
    	//$this->assign("wecha_shop",$wecha_shop);
    	$this->assign('item',$result);
    	$this->assign("item_taobao",$item_taobao);
    	$this->assign("title","全网比价");
    	$this->assign("brand",$brand_data);
    	$this->display();
    }
    public function nextPageBrand($token,$brandid,$itemid,$sortBy){
    	$tokenTall = $token;
    	$this->assign('tokenTall',$tokenTall);
    	$this->assign("City","附近店铺");
    	$this->assign("gosearch","Y");
    	$this->assign("brand_id",$brandid);
    	 
    	$item = M("item_taobao");
    	$condition["brand"] = $brandid;
    	if(!empty($itemid)){
    		$condition["cate_id"]=$itemid;
    	}
    	$brand_id["id"] = $brandid;
    	$brand_name = M("brandlist")->where($brand_id)->find();
    	$this->assign("title",$brand_name["name"]);
    	
    	$count = $item->where($condition)->count();
    	$Page       = new Page($count,$count);// 实例化分页类 传入总记录数
    	// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
    	$nowPage = isset($_GET['p'])?$_GET['p']:1;
    	$show       = $Page->show();// 分页显示输出
    	$carryrecord  = $item->where($condition)->order($sortBy)->limit($Page->firstRow.','.$Page->listRows)->select();
    
    	$this->assign("item",$carryrecord);
    	$this->assign("compare","Y");
    	$this->assign("itemcate","Y");
    	$this->assign('page',$show);// 赋值分页输出pti
    	$this->assign("count",$count);
    	$this->display();
    }
    public function nextPageCate($token,$itemid,$sortBy){
    	$tokenTall = $token;
    	$this->assign('tokenTall',$tokenTall);
    	
    	$item = M("item_taobao");
    	//if($token != ""){
    	//	$condition["tokenTall"]=$token;
    	//}
    	$condition["cate_id"] = $itemid;

    	$count = $item->where($condition)->count();
    	$Page       = new Page($count,$count);// 实例化分页类 传入总记录数
    	// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
    	$nowPage = isset($_GET['p'])?$_GET['p']:1;
    	$show       = $Page->show();// 分页显示输出
    	$carryrecord  = $item->where($condition)->order($sortBy)->limit($Page->firstRow.','.$Page->listRows)->select();
    	 
    	$this->assign("item",$carryrecord);
    	$this->assign("itemcate","Y");
    	$this->assign('page',$show);// 赋值分页输出pti
    	$this->assign("count",$count);
    	$this->display();
    }
    public function nextPage($method,$keyword,$sortBy,$token){
    	if($method=="shop"){   		
    		$item = M("wecha_shop");
    		$condition["name"] = array("like", "%".$keyword."%");
    		$count = $item->where($condition)->count();
    		$Page       = new Page($count,$count);// 实例化分页类 传入总记录数
    		// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
    		$nowPage = isset($_GET['p'])?$_GET['p']:1;
    		$show       = $Page->show();// 分页显示输出
    		$carryrecord  = $item->where($condition)->order('credit DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
    		
    		foreach ($carryrecord as $val){    			
    			$val["descr"]=mb_substr($val["descr"], 0,35,"utf-8")."...";
    			$carryrecord2[]=$val;
    		}
 
    		$this->assign("item",$carryrecord2);
    		$this->assign("method",$method);
    		$this->assign('page',$show);// 赋值分页输出pti
    		$this->assign("count",$count);
    		$this->display();
    	}else{
	    	$tokenTall = $token;
	    	$this->assign('tokenTall',$tokenTall);
	    	//echo $keyword."hi";die();
	    	$item = M("item_taobao");
	    	if($token != "" & $_SESSION["search_all"] != "Y"){
	    	   $condition["tokenTall"]=$token;
	    	}
	    	$first["title"]=array("like", "%".$keyword."%");
	    	$count = $item->where($first)->count();
	    	if ($_SESSION["search_all"] != "Y") {
		    	if ($count == 0) {
		    		$condition["Huohao"] = array("like", "%".$keyword."%");
		    	}else{
		    		$condition["title"] = array("like", "%".$keyword."%");
		    	}
	    	}
	    	
	    	
	    	$brand = M("brandlist")->select();
	    	$this->assign("brand",$brand);
	    	
	    	$count = $item->where($condition)->count();
	    	$Page       = new Page($count,$count);// 实例化分页类 传入总记录数
	    	// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
	    	$nowPage = isset($_GET['p'])?$_GET['p']:1;
	    	$show       = $Page->show();// 分页显示输出
	    	$carryrecord  = $item->where($condition)->order($sortBy)->limit($Page->firstRow.','.$Page->listRows)->select();
	    	 
	    	$this->assign("item",$carryrecord);
	    	if ($_SESSION["search_all"] != "Y") {
	    		$this->assign("method",$method);
	    	}else{
	    		$this->assign("method","");
	    		$this->assign("gohref","Y");
	    		$this->assign("title","所有商品");
	    	}
	    	
	    	$this->assign('page',$show);// 赋值分页输出pti
	    	$this->assign("count",$count);
    		
	    	$this->display();
    	}
    }
    public function getItem($where = array())
    {
    	$where_init = array('status'=>'1');
        $where =array_merge($where_init, $where);
    	
    	return $item=M('item')->where($where)->select();
    }
    
    public function brandshop(){
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    
    	
    	$filter = $this->_get("order","trim");
    	if ($filter == "") {
    		$filter = 'volume';
    		$order = $filter." desc";
    	}elseif ($filter == "name"){
    		$order = $filter." asc";
    	}elseif($filter == "volume"){
    		$order = $filter." desc";
    	}
    	  	
    	if (IS_POST) {
    		$brandname = $this->_post("txtkeyword","trim");
    		$method = $this->_post("method","trim");
    		$_SESSION["paixu_place"] = $method;
    		$where["name"] = array("like","%".$brandname."%");
    	}
    	
    	if ($_SESSION["paixu_place"] == "") {
    		$method = "item";
    	}else{
    		$method = $_SESSION["paixu_place"];
    	}
    	
    	$this->assign("gowhere",$method);
    	
    	$brand = M("brandlist")->where($where)->order($order)->select();
    	$this->assign("brand",$brand);
    	$this->display();
    }
    public function setlat(){
    	$lat=$this->_get("lat","trim");
    	$lng=$this->_get("lng","trim");
    	$_SESSION["lat"]=$lat;
    	$_SESSION["lng"]=$lng;
    	$this->ajaxReturn("1",'成功','状态');
    }
    public function ajaxLogin()
    {
    	
        $user_name=$_POST['user_name'];
        $password=$_POST['password'];
       
        $user=M('user');
        $users= $user->where("username='".$user_name."' and password='".md5($password)."'")->find(); 
        if(is_array($users))
        {
        	$tokenTall = $this->getTokenTall();
    		$data = array('status'=>1, 'url'=>U('user/index', array('tokenTall'=>$tokenTall)));
    		$_SESSION['user_info']=$users;
    		$user->where("username='".$user_name."' and password='".md5($password)."'")->save(array('last_login_time'=>time()));
        }else {
       		$data = array('status'=>0);
        }
    	
		echo json_encode($data);
    	exit;
    }
    
    public function ajaxRegister()
    {
    	$username = $_GET['user_name'];
    	$user = M('user');
    	$count = $user->where("username='".$username."'")->find();
    	if(is_array($count))
    	{
        	echo 'false';
    	}else 
    	{
    		echo 'true';
    	}
    }
    
    public function ajaxCheckuser()
    {
    	$username = $_GET['user_name'];
    	$user = M('user');
    	$count = $user->where("username='".$username."'")->find();
    	if(is_array($count))
    	{
    		echo 'true';
    	}
    	else
    	{
    		echo 'false';
    	}
    	
    }
    //收藏
    public function favi()
    {
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	//dump($_SESSION);exit;
    	//0-未登录 1-保存成功 2-保存失败 3-无动作类型
    	header("content-Type: text/html; charset=Utf-8");
    	$tokenTall = $this->getTokenTall();
    	if($_POST['act']){
    		$act = $_POST['act'];
    		$item_id = $act;
    		if ($_SESSION['user_info']) {
	    		$userid = $_SESSION['user_info']['id'];
	    		$shopfav_mod = M('shop_favi');
	    		$insdata = array('userid'=>$userid, 'item_id'=>$item_id);
	    		if ($shopfav_mod->where($insdata)->find()) {
	    			//已经有记录的情况下
	    			
	    				$data = array('status'=>2);
	    			
	    			
	    		}else{
	    			
	    				//收藏
		    			if ($shopfav_mod->add($insdata)) {
		    				//成功
		    				$data = array('status'=>1);
		    				$item["id"] = $item_id;
		    				$item_data = M("item")->where($item)->find();
		    				$item_data_new["favi"]=$item_data["favi"]+1;
		    				M("item")->where($item)->save($item_data_new);
		    			}else{
		    				//失败
		    				$data = array('status'=>2);
		    			}
	    			
	    		}
	    	}else{
	    		//当前未登录
	    		$data = array('status'=>0, 'url'=>U('user/index', array('tokenTall'=>$tokenTall)));
	    	}
    	}else{
    		//没有动作类型
    		$data = array('status'=>3);
    	}
    	
    	echo json_encode($data);
    }
    
    
    //商家信息
    public function shopinfo()
    {
    	
    	/*店铺信息*/
    	$weChaShop = M("wecha_shop");
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	if($tokenTall == ""){
    		$weshopData["tokenTall"] = $_SESSION["tokenTall"];
    	}else{
    		$weshopData["tokenTall"] = $tokenTall;
    	}
    	$shop_id=$this->_get("id","trim");
    	$weChaShopDetail = $weChaShop->where($weshopData)->find();//var_dump($weshopData);die();
    	if (empty($weChaShopDetail)) {
    		$data['id']=$shop_id;
    		$weChaShopDetail=$weChaShop->where($data)->find();
    	}
    	$this->assign("weshopData",$weChaShopDetail);

//dump($weChaShopDetail["name"]);

		/*创店时间*/
    	$weUser = M("wxuser");
    	$weUserDetail = $weUser->where($weshopData)->find();//var_dump($weshopData);die();
    	$weUserDetail["createtime"] = date('Y-m-d h:m:s',$weUserDetail["createtime"]);
    	$this->assign("wxuserData",$weUserDetail);
//dump($weUserDetail["createtime"]);
    	
    	/*宝贝数量*/
    	$weItem = M("item");
    	$weItemCount = $weItem->where($weshopData)->count();//var_dump($weshopData);die();
    	$this->assign("weItemCount",$weItemCount);
//dump($weItemCount);

    	/*人气指数*/
    	$weShopFavi = M("shop_favi");
    	$weShopFaviCount = $weShopFavi->where($weshopData)->count();//var_dump($weshopData);die();
    	$this->assign("weshopFaviCount",$weShopFaviCount);
//dump($weShopFaviCount);

    	/*好评率*/
    	$item_id["item_id"]= $weChaShopDetail["id"];
    	$allNum = M("comments")->count();
    	$goodNum = M("comments")->where($item_id)->count();
    	
    	$rate = $goodNum/$allNum*100;
    	$this->assign("rate",$rate);
//dump($allNum);
//dump($goodNum);
//dump($rate);    	
//dump($weChaShopDetail["phone"]);
//die();

		
    	 
    	
    	$this->display();
    	
    }    
    	 
    public function promotion(){
    	//获取地理位置
    	$this->get_loaction();
    	
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	$wecha_shop = M("upload_shop");
    	$longitude = $this->_POST("longitude","trim");
    	$latitude = $this->_POST("latitude","trim");
    	if ($latitude == "") {
    		$longitude = $_SESSION["longtitude"] ;
    		$latitude = $_SESSION["latitude"] ;
    	}else{
    		$_SESSION["longtitude"] = $longitude;
    		$_SESSION["latitude"] = $latitude;
    	}
    	
    	//检查促销情况，自动设置促销状态
    	$alldata = M('set_promotion')->select();
    	foreach ($alldata as $onedata){
    		$status = $this->checkPromotion($onedata['start_date'], $onedata['end_date']);
    		M('set_promotion')->where(array('id'=>$onedata['id']))->save(array('status'=>$status));
    	}
    	
    	$Model = new Model();
    	$volumn = $Model->query("select b.name, a.img, a.theme, a.discount_rate, a.tokenTall from tp_set_promotion a, tp_brandlist b where a.brand_id=b.id and a.status=1;");
    	$brand_name = "";
    	foreach ($volumn as $val){
    		$brand_name .= $val['name'].',';
    	}
    	$where["brand_name"] = array('in',$brand_name);
    	
    	$volumn2 = $Model->query("select b.name, a.img, a.theme, a.discount_rate, a.tokenTall from tp_set_promotion a, tp_wecha_shop b where a.tokenTall=b.tokenTall and a.status=1;");
    	$shop_name = "";
    	foreach ($volumn2 as $val2){
    		$shop_name .= $val2['name'].',';
    	}
    	$where["shop_name"] = array('in',$shop_name);
    	 
    	$endPoint = $wecha_shop->where($where)->select();
    	$nearShop=array();
    	if ($longitude != "" and $latitude != "") {
    		foreach ($endPoint as $end){
    			if ($end["lbs_addr"] != "" and $end["longtitude"] != "") {
    				$end["nearJuli"] = $this->GetDistance($latitude,$longitude,$end["lat"],$end["longtitude"]);
    				//echo $end["nearJuli"]."--0".$latitude."----1<br>".$longitude."----2<br>".$end["lat"]."----3<br>".$end["longtitude"]."===".$end["nearJuli"];die();
    				$nearShop[] = $end;
    			}
    		}
    	}
    	
    	$start_point_lat = $latitude;
    	$start_point_lng = $longitude;
    	
    	//排序
    	$new_nearShop = $this->array_sort($nearShop,"nearJuli", "asc");
    	$length = count($nearShop);
    	
    	//促销信息
    	$promotion_theme = array();
		$promotion_discount = array();
    	foreach ($volumn as $val){
    		$promotion_theme[$val['name']] = $val['theme'];
    		$promotion_discount[$val['name']] = $val['discount_rate'];
    	}
    	$this->assign("promotion_theme",$promotion_theme);
		$this->assign("promotion_discount",$promotion_discount);
    	
    	$this->assign("title","店内促销");
    	$this->assign("countShop",$length);
    	$this->assign("start_point_lat",$start_point_lat);
    	$this->assign("start_point_lng",$start_point_lng);
    	$this->assign("searchNear","Y");
    	$this->assign("nearShop",$new_nearShop);
    	$this->assign("promotion",$volumn[0]);
    	
    	//广告生效和读取 begin
    	$alldata = M('adforhome')->where(array('checkstatus'=>1))->select();
    	foreach ($alldata as $onedata){
    		$status = $this->checkPromotion(date('Y-m-d',$onedata['start_time']), date('Y-m-d',$onedata['end_time']));
    		M('adforhome')->where(array('id'=>$onedata['id']))->save(array('status'=>$status));
    	}
    	$data["status"]=1;
    	$data["checkstatus"]=1;
    	$data["boadid"]=array(array('eq',1),array('eq',2),array('eq',3),'or');
    	$weTallboard = M("adforhome")->where($data)->order("id asc")->select();
    	$this->assign("weTallboard",$weTallboard);
    	//dump($weTallboard);exit;
    	//广告生效和读取  end
    	
    	$url = "http://api.map.baidu.com/geocoder?location=".$latitude.",".$longitude."&output=xml&key=28bcdd84fae25699606ffad27f8da77b";
    	//$url = "http://api.map.baidu.com/geocoder?location=31.256748,121.595578&output=xml&key=28bcdd84fae25699606ffad27f8da77b";
    	$city_data = file_get_contents($url);
    	preg_match('/<city>.*<\/city>/',$city_data,$total_page);
    	//$city_info = iconv('GBK', 'UTF-8',$total_page[0]);echo $city_info;die();
    	$currentcity = preg_replace('/市/',"",$total_page[0]);
    	$this->assign("City",$currentcity);
    	 
    	$this->display();
    		
    }
    
    public function addMatch() {
    	
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	 
    	$this->display();
    }    
    //分享搭配留言
    public function comments(){
    	$where["id"] = $_GET["id"];
    	$match_coment=M("match");
    	$result = $match_coment->where($where)->find();
        $match_table[] = $result;
        $match_table[0]['create_time']=fdate($result['create_time']);
    	$username = M("user")->where("id='".$result['uid']."'")->find();
    	$match_table[0]["uname"] = $username["nickname"];
    	//评论人员
    	$data["match_id"] = $_GET["id"];
    	$p_match = M("match_comments");
    	$result2 = $p_match->where($data)->order("addtime DESC")->select();
    	$match_comment = array();
    	$index=0;
    	foreach($result2 as $match_c){
    		$match_comment[] = $match_c;
    		$username2 = M("user")->where("id='".$match_c['uid']."'")->find();
    		$match_comment[$index]["uname"] = $username2["nickname"]; 
    		$match_comment[$index]["userimgurl"] = $username2["headimgurl"];
    		$match_comment[$index]['addtime']=fdate($match_c["addtime"]);
    		$index++;
    	}
    	$this->assign("match_comment",$match_table);
    	$this->assign("match_p",$match_comment);
    	$this->display();
    }
    //添加搭配分享评论
    public function add_comments(){
    	$tokenTall=$_GET['tokenTall'];
    	if($_SESSION['uid']==''){
    		$this->success("页面已失效","{:U('index/match',array('tokenTall'=>$tokenTall))}");
    	}else{
    		$data['match_id']=$this->_post('match_id','intval');
    		$data['uid']=$_SESSION['uid'];
    		$data['comments']=$this->_post('comments');
    		$data['addtime']=time();
    		if(M('match_comments')->add($data)){
    			$this->success('评论成功');
    		}else{
    			$this->success('服务器繁忙，请稍后再试！');
    		}
    	}
    } 
    //点赞
    public function add_love(){
    	if(!isset($_SESSION['uid']) && $_SESSION['uid']==''){
    		echo '0';//页面已过期
    	}else{
    		$M_love = M('match_love');
    		$data['matchid']= $_POST['matchid'];
    		$data['uid']=$_SESSION['uid'];
    		$res=$M_love->where($data)->find();
    		if(empty($res)){
    			if($M_love->add($data)){
    				$sun_l=$M_love->where("matchid='".$data['matchid']."'")->count();
    				echo ''.$sun_l;//成功
    			}else{
    				echo '3';
    			}
    		}else{
    			echo '4';//已赞
    		}

    	}
    }
}
