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
    public function saohuo(){
    	$brand_id=$this->_get("brand_id","trim");
    	$id=$this->_get("id","intval");
    	$set_discount=M("set_discount");
    	$discount["id"]=$id;
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
    public function addressselect(){
    	$upload_shop = M("item");
    	$color = $_GET["color"];
    	$size = $_GET["size"];
    	
    	$where["id"] = $_GET["item_id"];
    	$result2 = $upload_shop->where($where)->find();
        $detail_stock=explode(",", $result2["detail_stock"]);
        foreach ($detail_stock as $stock){
        	$stock_real=explode("|", $stock);
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
    
    public function test(){  	
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
    	if (IS_POST) {
    		$wecha_shop = M("upload_shop");
    		$longitude = $this->_POST("longitude","trim");
    		$latitude = $this->_POST("latitude","trim");
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
    		if ($longitude != "") {
	    		foreach ($endPoint as $end){    
	    			if ($end["lbs_addr"] != "") {		
	    				$end["nearJuli"] = $this->GetDistance($latitude,$longitude,$end["lat"],$end["longtitude"]);
	    				//echo $latitude,$longitude,$end["latitude"],$end["longitude"];die();
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
    public function search() {
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	
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
    		//搜索的方式本店，微服客，店铺
    		$method=$this->_post("method");
    		
    		$tokenTall = $this->getTokenTall();
    		if($tokenTall != ""){
    			$token= $tokenTall;
    		}else{
    			$token=$_SESSION["tokenTall"];
    		}
    		$this->assign("method",$method);
    		if($keyword == ""){
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
    			$this->nextPageBrand($_SESSION['token'],$brandid,$sortBy);
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
    	
    	$item = M("item");
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
	    	$Page       = new Page($count,10);// 实例化分页类 传入总记录数
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
    	$Huohao["Huohao"] = $this->_get("Huohao","trim");
    	$item_huohao = M("item")->where($Huohao)->select();
    	$item_taobao = M("item_taobao")->where($Huohao)->find();
    	$brand["id"] = $item_taobao["brand"];
    	$brand_data = M("brandlist")->where($brand)->find();
    	
    	$this->assign("item",$item_huohao);
    	$this->assign("item_taobao",$item_taobao);
    	$this->assign("brand",$brand_data);
    	$this->display();
    }
    public function nextPageBrand($token,$itemid,$sortBy){
    	$tokenTall = $token;
    	$this->assign('tokenTall',$tokenTall);
    	 
    	$item = M("item_taobao");
    	$condition["brand"] = $itemid;
    	$count = $item->where($condition)->count();
    	$Page       = new Page($count,10);// 实例化分页类 传入总记录数
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
    	
    	$item = M("item");
    	//if($token != ""){
    	//	$condition["tokenTall"]=$token;
    	//}
    	$condition["cate_id"] = $itemid;
    	$count = $item->where($condition)->count();
    	$Page       = new Page($count,10);// 实例化分页类 传入总记录数
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
    		$Page       = new Page($count,10);// 实例化分页类 传入总记录数
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
	    	$item = M("item");
	    	if($token != ""){
	    	   $condition["tokenTall"]=$token;
	    	}
	    	$first["title"]=array("like", "%".$keyword."%");
	    	$count = $item->where($first)->count();
	    	if ($count == 0) {
	    		$condition["Huohao"] = array("like", "%".$keyword."%");
	    	}else{
	    		$condition["title"] = array("like", "%".$keyword."%");
	    	}
	    	
	    	$brand = M("brandlist")->select();
	    	$this->assign("brand",$brand);
	    	
	    	$count = $item->where($condition)->count();
	    	$Page       = new Page($count,10);// 实例化分页类 传入总记录数
	    	// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
	    	$nowPage = isset($_GET['p'])?$_GET['p']:1;
	    	$show       = $Page->show();// 分页显示输出
	    	$carryrecord  = $item->where($condition)->order($sortBy)->limit($Page->firstRow.','.$Page->listRows)->select();
	    	 
	    	$this->assign("item",$carryrecord);
	    	$this->assign("method",$method);
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
    	
    	$filter = $this->_get("filter","trim");
    	if ($filter == "guonei") {
    		$where["domain"] = 0;
    	}elseif ($filter == "luxury"){
    		$where["domain"] = 1;
    	}
    	
    	if (IS_POST) {
    		$brandname = $this->_post("txtkeyword","trim");
    		$method = $this->_post("method","trim");
    		$where["name"] = array("like","%".$brandname."%");
    		$this->assign("gowhere",$method);
    	}
    	
    	$brand = M("brandlist")->where($where)->order("volume desc")->select();
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
    	//if (IS_POST) {					
    		//map 功能需要post ?
    	    $longitude = $this->_POST("longitude","trim");
	        $latitude = $this->_POST("latitude","trim");
	        
	        /***商品分类**/
	        $item_cate=M("item_cate")->select();
	        $this->assign('item_cate',$item_cate);
    	    //
	        //xxl
	        //$brand_id = $this->_POST("brand_id","trim");
	        //$brand_data['id'] = $brand_id;

	        //取商家token值，取不到则默认为空
	        $tokenTall = $this->getTokenTall();
	        $_SESSION["tokenTall"]=$tokenTall;	        
	        //广告位
	        $weTallad = M("adforhome");
	        $data["status"]=1;
	        $data["checkstatus"]=1;
	        $data["boadid"]=array(array('eq',1),array('eq',2),array('eq',3),'or');
	        $weTallboard = $weTallad->where($data)->order("id asc")->select();
	        $this->assign("weTallboard",$weTallboard);	        
	        /*****首页广告end******/	        
	        
	        //promotion event
			$Sel_sql = "SELECT s.id,s.theme, s.start_date, s.end_date, w.name FROM tp_set_promotion s, tp_wecha_shop w ";
		    $Where_sql = "WHERE s.tokentall = w.tokentall  AND s.status = 1 AND w.shop_city =  '上海' " ;
		    					
	        
	        $m=M();	        
	        $result=$m->query($Sel_sql.$Where_sql);

	        
	        $this->assign("nearPromotion",$result);
	        $this->assign("countPromotion",count($result));
			
	        
	        
    	//}
    	 
    	$this->assign("City","北京");
    	$this->assign("title","店内促销");
    	$this->display();
    }    
    
    public function promotioninfo(){
    	
    	$keyword = NULL;
    	/***商品分类**/
    	$item_cate=M("item_cate")->select();
    	$this->assign('item_cate',$item_cate);
    	if (IS_POST) {
    		$keyword = $this->_POST("keyword","trim");	
    	}
    	//promotion event
    	$id = $this->_get("id","trim");
    	$Sel_sql = "SELECT i.id,b.name AS brand_name, i.title AS title, s.discount_rate * i.price /100 AS price, i.item_model, i.img ";
    	$From_sql ="FROM tp_set_promotion s, tp_item i, tp_brandlist b ";
    	$Where_sql = "WHERE s.id = i.promotion_id AND i.brand = b.id AND s.tokentall = i.tokentall AND s.id =".$id." ";
    	if($keyword != NULL){
    		$like = "And i.title like '%".$keyword."%' "; 
    		$Where_sql = $Where_sql.$like;      		
    	}
    	     	 
    	$m=M();
	
    	$result=$m->query($Sel_sql.$From_sql.$Where_sql);
    	$this->assign("promotioninfo",$result);
    	
    	//promotion event
    	$Sel_sql = "SELECT w.name FROM tp_set_promotion s, tp_wecha_shop w ";
    	$Where_sql = "WHERE s.tokentall = w.tokentall AND w.shop_city =  '上海' AND s.id =".$id;  	
    	$result=$m->query($Sel_sql.$Where_sql);    	 	
    	$this->assign("name",$result[0]['name']);
    	$this->assign("id",$id);
    	$this->assign("keyword",$keyword);
    	
    	$this->display();
    }   

    public function match() {
    	$discount_shop = M("set_discount");
    	$brand = M("brandlist");
    	$discount_data = $discount_shop->order("date asc")->group("status")->select();
    	 
    	$this->assign("brand",$brand->select());
    	$this->assign("ontime",$discount_data);
    	$this->display();
    }
    
    public function addMatch() {
    	$this->display();
    }    

}
