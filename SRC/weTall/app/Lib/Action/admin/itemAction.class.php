<?php
class itemAction extends backendAction {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('item');
        $this->_cate_mod = D('item_cate');
        $brandlist= $this->_brand=M('brandlist')->where('status=1')->order('ordid asc,id asc')->select();
        $this->assign('brandlist',$brandlist);
    }

    public function _before_index() {
        //显示模式
        $sm = $this->_get('sm', 'trim');
        $this->assign('sm', $sm);

        //分类信息
        $res = $this->_cate_mod->field('id,name')->select();
       
        $cate_list = array();
        foreach ($res as $val) {
            $cate_list[$val['id']] = $val['name'];
        }
      
        $this->assign('cate_list', $cate_list);

        //默认排序
        $this->sort = 'ordid ASC,';
        $this->order ='add_time DESC';
    }

    protected function _search() {
        $map = array();

        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        ($price_min = $this->_request('price_min', 'trim')) && $map['price'][] = array('egt', $price_min);
        ($price_max = $this->_request('price_max', 'trim')) && $map['price'][] = array('elt', $price_max);
        ($uname = $this->_request('uname', 'trim')) && $map['uname'] = array('like', '%'.$uname.'%');
        $cate_id = $this->_request('cate_id', 'intval');
        if ($cate_id) {
            $id_arr = $this->_cate_mod->get_child_ids($cate_id, true);
            $map['cate_id'] = array('IN', $id_arr);
            $spid = $this->_cate_mod->where(array('id'=>$cate_id))->getField('spid');
            if( $spid==0 ){
                $spid = $cate_id;
            }else{
                $spid .= $cate_id;
            }
        }
        if( $_GET['status']==null ){
            $status = -1;
        }else{
            $status = intval($_GET['status']);
        }
        $status>=0 && $map['status'] = array('eq',$status);
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%'.$keyword.'%');
        
        $tokenTall = $this->getTokenTall();
        $map['tokenTall'] = array('eq', $tokenTall);
        
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'price_min' => $price_min,
            'price_max' => $price_max,
            'uname' => $uname,
            'status' =>$status,
            'selected_ids' => $spid,
            'cate_id' => $cate_id,
            'keyword' => $keyword,
        	'tokenTall' => $tokenTall,
        ));
        return $map;
    }

    public function add() {
    	
    	$tokenTall = $this->getTokenTall();
    	$this->assign('tokenTall',$tokenTall);
        if (IS_POST) {
        	//得到商品的尺码和颜色
        	$colors = $_POST['color'];
        	$colorstr = "";
        	foreach($colors as $val){
        		$colorstr = $colorstr."|".$val;
        	}
            $sizes = $_POST['size'];
        	$sizestr = "";
        	foreach($sizes as $val2){
        		$sizestr = $sizestr."|".$val2;
        	}
        	
            //获取数据
            if (false === $data = $this->_mod->create()) {
                $this->error($this->_mod->getError());
            }
            if( !$data['cate_id']||!trim($data['cate_id']) ){
                $this->error('请选择商品分类');
            }
           
            if($_POST['brand']==''){
            	
                $this->error('请选择品牌');
            }
         
            
            //必须上传图片
            if (empty($_FILES['img']['name'])) {
                $this->error('请上传商品图片');
            }
           if(isset($_POST['news']))
            {
            	$data['news']=1;
            }else {
            	$data['news']=0;
            }
             if(isset($_POST['tuijian']))
            {
            	$data['tuijian']=1;
            }else {
            	$data['tuijian']=0;
            }

            if($_POST['free']==1)
            {
            	$data['free']=1;
            }else if($_POST['free']==2)
            {
            $data['free']=2;
            $data['pingyou']=$this->_post('pingyou');
            $data['kuaidi']=$this->_post('kuaidi');
            $data['ems']=$this->_post('ems');
            }

            //上传图片
            $date_dir = date('ym/d/'); //上传目录
            $item_imgs = array(); //相册
            $result = $this->_upload($_FILES['img'], 'item/'.$date_dir, array(
                'width'=>C('pin_item_bimg.width').','.C('pin_item_img.width').','.C('pin_item_simg.width'), 
                'height'=>C('pin_item_bimg.height').','.C('pin_item_img.height').','.C('pin_item_simg.height'),
                'suffix' => '_b,_m,_s',
                //'remove_origin'=>true 
            ));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $data['img'] = $date_dir . $result['info'][0]['savename'];
                //保存一份到相册
                $item_imgs[] = array(
                    'url'     => $data['img'],
                );
            }
            //上传相册
            $file_imgs = array();
            foreach( $_FILES['imgs']['name'] as $key=>$val ){
                if( $val ){
                    $file_imgs['name'][] = $val;
                    $file_imgs['type'][] = $_FILES['imgs']['type'][$key];
                    $file_imgs['tmp_name'][] = $_FILES['imgs']['tmp_name'][$key];
                    $file_imgs['error'][] = $_FILES['imgs']['error'][$key];
                    $file_imgs['size'][] = $_FILES['imgs']['size'][$key];
                }
            }
            if( $file_imgs ){
                $result = $this->_upload($file_imgs, 'item/'.$date_dir, array(
                    'width'=>C('pin_item_bimg.width').','.C('pin_item_simg.width'),
                    'height'=>C('pin_item_bimg.height').','.C('pin_item_simg.height'),
                    'suffix' => '_b,_s',
                ));
                if ($result['error']) {
                    $this->error($result['info']);
                } else {
                    foreach( $result['info'] as $key=>$val ){
                        $item_imgs[] = array(
                            'url'    => $date_dir . $val['savename'],
                            'order'  => $key + 1,
                        );
                    }
                }
            }
            $data['imgs'] = $item_imgs;
            $data['tokenTall'] = $tokenTall;
            //加入颜色和尺码
            $data["size"]=$sizestr;
            $data["color"]=$colorstr;
            
            $item_id = $this->_mod->publish($data);
            !$item_id && $this->error(L('operation_failure'));
            $this->success(L('operation_success'));
        } else {
            $this->display();
        }
    }

    public function data_update() {
    	$mod = D($this->_name);
    	if (IS_POST) {
    		if (false === $data = $mod->create()) {
    			IS_AJAX && $this->ajaxReturn(0, $mod->getError());
    			$this->error($mod->getError());
    		}    

    		if (isset($_POST['url'])):
    		$tianmao_urls = $_POST['url'];
    		/**
    		 * 取得店铺所有商品的ID
    		 */
    		//$this->get_good_attr($tianmao_urls);/*
    		$item_search = $tianmao_urls."&search=y";
    		if (strstr($tianmao_urls,"tmall") == true) {
    		$content_page = file_get_contents($item_search);
    		preg_match('/class=\"ui-page-s-len\".*b>/',$content_page,$total_page);
    		 
    		}elseif(strstr($tianmao_urls,"taobao") == true){
    		$content_page = file_get_contents($item_search);
    		preg_match('/class=\"page-info\".*span>/',$content_page,$total_page);
    		}else{
    		$total_page1 = 0;
    		}
    		$total_page1 = explode("/",$total_page[0]);
    
    
    		$pageNo = 1;
    		$current_url = $item_search; //初始url
    		$url_array = array();
    		do{
    		$current_url = $item_search."&pageNo=".$pageNo;
    		$result_url_arr = $this->crawler($current_url);
    		if ($result_url_arr) {
    		foreach ($result_url_arr as $url) {
    		$url_array[] = $url;
    		}
    		}
    		$pageNo = $pageNo + 1;
    		}while ($pageNo - 1 < $total_page1[1]);
    		//var_dump($url_array);die();
    		$failed_num = 0;
    		$success_num = 0;
    		foreach ($url_array as $good_url){
    		
	    		if ($this->check_good_attr($good_url)) {
	    		    $failed_num = $failed_num + 1;
	    		}else{
	    		    $success_num = $success_num + 1;
	    		}
    		}
    		// echo $success_num.$failed_num;die();
    		$msg_su = "没有数据可以更新";
    		$haveupdate = "你有".$success_num."个商品可以同步下来";
    		//  	*/
    		if ($success_num == 0) {
    			IS_AJAX && $this->ajaxReturn(1, $msg_su, '', 'add');
    			$this->success($msg_su);
    		}else{
    			IS_AJAX && $this->ajaxReturn(0, $haveupdate,'','add');
    			$this->success($haveupdate);
    		}
    		endif;
    	} else {
    		$this->assign('open_validator', true);
    		if (IS_AJAX) {
    			$response = $this->fetch();
    			$this->ajaxReturn(1, '', $response);
    		} else {
    			$this->display();
    		}
    	}
    }
    public function data_excel() {
    	$mod = D($this->_name);
    	if (IS_POST) {
    		if (false === $data = $mod->create()) {
    			IS_AJAX && $this->ajaxReturn(0, $mod->getError());
    			$this->error($mod->getError());
    		}
    		
    		if (isset($_POST['url'])):
	    		$tianmao_urls = $_POST['url'];
    			$brandid["name"] = $_POST['brandid'];
    			$brandid_arr = M("brandlist");
    			$bid = $brandid_arr->where($brandid)->field("id")->find();
    			if(empty($brandid["name"])){
    				echo "请输入品牌！";die();
    			}elseif(!empty($bid["id"])){
    				$item["brand"] = $bid["id"];
    			}else {
    				$brandid_arr->add($brandid);
    				 $bid2 = $brandid_arr->where($brandid)->field("id")->find();
    				 $item["brand"] = $bid2["id"];
    			}
    			
    		/**
    		 * 取得店铺所有商品的ID
    		 */
    			//$this->get_good_attr($tianmao_urls);/*
                $item_search = $tianmao_urls."&search=y";
                if (strstr($tianmao_urls,"tmall") == true) {
                	$content_page = file_get_contents($item_search);
                	preg_match('/class=\"ui-page-s-len\".*b>/',$content_page,$total_page);
                	
                }elseif(strstr($tianmao_urls,"taobao") == true){
                	$content_page = file_get_contents($item_search);
                	preg_match('/class=\"page-info\".*span>/',$content_page,$total_page);
                }else{
                	$total_page1 = 0;
                }
                $total_page1 = explode("/",$total_page[0]);

    		
                $pageNo = 1;
    			$current_url = $item_search; //初始url
    			$url_array = array();
    			do{  
    				$current_url = $item_search."&pageNo=".$pageNo;
    				$result_url_arr = $this->crawler($current_url);
    				if ($result_url_arr) {
    					foreach ($result_url_arr as $url) {
    						$url_array[] = $url;
    					}
    				}	    			
	    			$pageNo = $pageNo + 1;
    			}while ($pageNo - 1 < $total_page1[1]);
    			//var_dump($url_array);die();
    			$failed_num = 0;
    			$success_num = 0;
    			foreach ($url_array as $good_url){
    				if ($success_num > 1) {
    					break;
    				}
    				if ($this->get_good_attr($good_url,$item["brand"])) {
    					$success_num = $success_num + 1;
    				}else{
    					$failed_num = $failed_num + 1;
    				}
    			}
    			
    			$msg_su = "成功导入".$success_num."个，有".$failed_num."个失败了！";
    			//  	*/
    			if ($success_num > 0) {
    				IS_AJAX && $this->ajaxReturn(1, $msg_su, '', 'add');
    				$this->success($msg_su);
    			}else{
    				IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
    				$this->error(L('operation_failure'));
    			}
    		endif;
    	} else {
    		$this->assign('open_validator', true);
    		if (IS_AJAX) {
    			$response = $this->fetch();
    			$this->ajaxReturn(1, '', $response);
    		} else {
    			$this->display();
    		}
    	}
    }
    /**
     * *验证数据是否可以更新
     */
    public function check_good_attr($url){
    	$text=file_get_contents($url);
    	//商品货号
    	$url_id = explode("id=",$url);
    	$url_id_real = explode("&",$url_id[1]);   
    	$item["Uninum"] = $url_id_real[0];
    	 
    	if (!empty($item["Uninum"])) {
    		if( $this->_mod->where($item)->find() ){
    		    return true;
    		} else {
    			return false;
    		}
    	}
    }
    /**
     * *获取商品数据
     */
    public function get_good_attr($url,$brand){
    	$text=file_get_contents($url);
    	//商品货号
    	$url_id = explode("id=",$url);
    	$url_id_real = explode("&",$url_id[1]);

    	$item["Uninum"] = $url_id_real[0];
    	//获取商品图片
    	preg_match('/<img[^>]*id="J_ImgBooth"[^r]*rc=\"([^"]*)\"[^>]*>/', $text, $img);
    	$result_imgs = preg_match_all('/<a href=\"#\"><img.*\/>/', $text,$imgs60);
    	$i = 0;
    	foreach ($imgs60 as $imgurl){
    		$i = $i + 1;
    		//str_replace("60x60","460x460",$imgurl);
    		$imgreal_url0=preg_replace('/<a.*><img/',"",$imgurl);  //去掉regular expression匹配出来的多余的东西 
    		$imgreal_url1=preg_replace('/.*src=\"/',"",$imgreal_url0);
    		$imgreal_url2=preg_replace('/\" \/>/',"",$imgreal_url1);
    		$imgreal_url=preg_replace('60x60',"460x460",$imgreal_url2);
    		
    		$newfile = "C:/Greyson/Weixin/".$i."hehe.jpg";
    		Http::curlDownload($imgreal_url,$newfile);  // 远程图片保存至本地
    		$imgsurl = $imgreal_url;
    	}
    	//var_dump($imgsurl);die();
    	
    	//商品尺码
    	preg_match_all('/<li data-value=\".*>.*<\/span><\/a><\/li>/', $text, $size);
    	foreach ($size[0] as $size1){
	    	$sizeurl = explode("<span>", $size1);
	    	$real_size = preg_replace('/<\/span><\/a><\/li>/',"",$sizeurl[1]);  //去掉regular expression匹配出来的多余的东西
	    	$result_size = $result_size."|".$real_size;
    	}
    	//var_dump($result_size);die();
    	
    	//商品颜色
    	preg_match_all('/<li.* title=.*>.*&#33394;.*<\/li>/', $text, $color);
    	foreach ($color[0] as $var_co) { 
    		$var_color = iconv('GB2312', 'UTF-8', $var_co);
    		if (strpos($var_color,"颜色")) {
    			$colorarr = $var_co;
    		}
    	}
    	//$colorarr = $color[0];
    	$color0 = explode(":",$colorarr);
    	//$real_color = preg_replace('/&nbsp;/',"",$color0[1]);
    	$color1 = explode("&nbsp;",$color0[1]);
    	foreach ($color1 as $var_color){
    		
    		if (!empty($var_color) and strlen($var_color) > 7) {
    			$colorresult = $colorresult."|".$var_color;
    		}
    	}

    	//var_dump($colorresult);die();
    	
    	//获取商品名称
    	preg_match('/<title>([^<>]*)<\/title>/', $text, $title);
    	//$title=iconv('GBK','UTF-8',$title);var_dump($title);
    	//获取商品价格
    	preg_match('/<strong class=\"J_originalPrice\">.*<\/strong>/',$text,$price); //正则表示获取包含价格的 HTML 标签
    	$price1 = preg_replace('/<strong class=\"J_originalPrice\">/',"",$price[0]);
    	$price2 = preg_replace('/<\/strong>/',"",$price1);
    	
    	//获取商品属性
    	preg_match('/<(div)[^c]*class=\"attributes\"[^>]*>.*<\/\\1>/is', $text, $text0);
    	$text1=preg_replace("/<\/div>[^<]*<(div)[^c]*id=\"description\"[^>]*>.*<\/\\1>/is","",$text0);
    	$attributes=preg_replace("/<\/div>[^<]*<(div)[^c]*class=\"box J_TBox\"[^>]*>.*<\/\\1>/is","",$text1);
    	$attributes1 = iconv('GB2312', 'UTF-8', $attributes[0]);
    	$attributes2 = preg_replace("/\\r\\n/","",$attributes1);
    	 
    	//获取商品描述
    	preg_match_all('/<script[^>]*>[^<]*<\/script>/is', $text, $content);//页面js脚本
    	$content=$content[0];
    	$description='<div id="detail" class="box"> </div>
		        <div id="description" class="J_DetailSection">
		          <div class="content" id="J_DivItemDesc">描述加载中</div>
		        </div>';
    	 
    	
    	foreach ($content as &$v){
    		$description.=iconv('GBK','UTF-8',$v);
    			
    	};
    	$img_real_url0=preg_replace('/<a.*><img/',"",$img[0]);  //去掉regular expression匹配出来的多余的东西
    	$img_real_url1=preg_replace('/.*src=\"/',"",$img_real_url0);   	
    	$item["img"] =preg_replace('/\".* \/>/',"",$img_real_url1);
    	$title_real = explode("-",$title[1]);
    	$item["title"] = iconv('GB2312', 'UTF-8', $title_real[0]);
    	$item["price"] = (float)$price2;
    	$item["info"] = $attributes2;
    	$item["size"] = iconv('GB2312', 'UTF-8', $result_size);
    	$item["color"] = $colorresult;
    	$item["brand"] = $brand;
    	//$item["imagesDetail"] = $description;
    	//var_dump($item);die();
    	
        if (!empty($item["Uninum"])) { 
        	if( !($this->_mod->where($item)->find()) ){      	
		    	if( $this->_mod->add($item) ){
		    		return true;
		    	} else {
		    		return false;
		    	}
        	}
    	}
    }
    /**
	 * 爬虫程序 -- 原型
	*
	* 从给定的url获取html内容
	*
	* @param string $url
	* @return string
	*/
	public function getUrlContent($url) {
		$handle = fopen($url, "r");
		if ($handle) {
			$content = stream_get_contents($handle, 1024 * 1024);
			return $content;
		} else {
			return false;
		}
	}
	/**
	 * 从html内容中筛选链接
	 *
	 * @param string $web_content
	 * @return array
	 */
	public function _filterUrl($web_content) {
		$reg_tag_a = '/<[a|A].*?href=[\'\"]{0,1}([^>\'\"\ ]*[\?|&]id=.*).*?>/';
		$result = preg_match_all($reg_tag_a, $web_content, $match_result);
		if ($result) {
			return $match_result[1];
		}
	}
	/**
	 * 修正相对路径
	 *
	 * @param string $base_url
	 * @param array $url_list
	 * @return array
	 */
	public function _reviseUrl( $url_list) {
		$flag = "X";$i = 0;
		foreach ($url_list as $url){$i = $i + 1;
			$result_url = explode("\"",$url);
			foreach ($result as $haveid){
				$flag = "X";
				if(strstr($result_url[0],$haveid ) == true or strstr($haveid,$result_url[0] ) == true){
					$flag = "Y";
					break;
				}
			}
			//if($i == 2) echo $flag;
			if($flag == "X") $result[] = $result_url[0];
		}
		return $result;
	}
	/**
	 * 爬虫
	 *
	 * @param string $url
	 * @return array
	 */
	public function crawler($url) {
		$content = $this->getUrlContent($url);//echo $content;
		if ($content) {
			$url_list = $this->_reviseUrl($this->_filterUrl($content));
			if ($url_list) {
				return $url_list;
			} else {
				return ;
			}
		} else {
			return ;
		}
	}
	
    public function edit() {
        if (IS_POST) {
            //获取数据
            if (false === $data = $this->_mod->create()) {
                $this->error($this->_mod->getError());
            }
            if( !$data['cate_id']||!trim($data['cate_id']) ){
                $this->error('请选择商品分类');
            }
            
             if($_POST['brand']==''){
            	
                $this->error('请选择品牌');
            }
            
              if($_POST['free']==1)
            {
            	$data['free']=1;
            	 $data['pingyou']=0;
            $data['kuaidi']=0;
            $data['ems']=0;
            }else if($_POST['free']==2)
            {
            $data['free']=2;
            $data['pingyou']=$this->_post('pingyou');
            $data['kuaidi']=$this->_post('kuaidi');
            $data['ems']=$this->_post('ems');
            } 
            
            
            $item_id = $data['id'];
            $date_dir = date('ym/d/'); //上传目录
            $item_imgs = array(); //相册
            //修改图片
            if (!empty($_FILES['img']['name'])) {
                $result = $this->_upload($_FILES['img'], 'item/'.$date_dir, array(
                    'width'=>C('pin_item_bimg.width').','.C('pin_item_img.width').','.C('pin_item_simg.width'), 
                    'height'=>C('pin_item_bimg.height').','.C('pin_item_img.height').','.C('pin_item_simg.height'),
                    'suffix' => '_b,_m,_s',
                ));
                if ($result['error']) {
                    $this->error($result['info']);
                } else {
                    $data['img'] = $date_dir . $result['info'][0]['savename'];
                    //保存一份到相册
                    $item_imgs[] = array(
                        'item_id' => $item_id,
                        'url'     => $data['img'],
                    );
                }
            }
         
            if(isset($_POST['news']))
            {
            	$data['news']=1;
            }else {
            	$data['news']=0;
            }
             if(isset($_POST['tuijian']))
            {
            	$data['tuijian']=1;
            }else {
            	$data['tuijian']=0;
            }
            //得到商品的尺码和颜色
            $colors = $_POST['color'];
            $colorstr = "";
            foreach($colors as $val){
            	$colorstr = $colorstr."|".$val;
            }
            $sizes = $_POST['size'];
            $sizestr = "";
            foreach($sizes as $val2){
            	$sizestr = $sizestr."|".$val2;
            }
            //加入颜色和尺码
            $data["size"]=$sizestr;
            $data["color"]=$colorstr;
            
            //上传相册
            $file_imgs = array();
            foreach( $_FILES['imgs']['name'] as $key=>$val ){
                if( $val ){
                    $file_imgs['name'][] = $val;
                    $file_imgs['type'][] = $_FILES['imgs']['type'][$key];
                    $file_imgs['tmp_name'][] = $_FILES['imgs']['tmp_name'][$key];
                    $file_imgs['error'][] = $_FILES['imgs']['error'][$key];
                    $file_imgs['size'][] = $_FILES['imgs']['size'][$key];
                }
            }
            if( $file_imgs ){
                $result = $this->_upload($file_imgs, 'item/'.$date_dir, array(
                    'width'=>C('pin_item_bimg.width').','.C('pin_item_simg.width'),
                    'height'=>C('pin_item_bimg.height').','.C('pin_item_simg.height'),
                    'suffix' => '_b,_s',
                ));
                if ($result['error']) {
                    $this->error($result['info']);
                } else {
                    foreach( $result['info'] as $key=>$val ){
                        $item_imgs[] = array(
                            'item_id' => $item_id,
                            'url'    => $date_dir . $val['savename'],
                            'order'   => $key + 1,
                        );
                    }
                }
            }
            //标签
            $tags = $this->_post('tags', 'trim');
            if (!isset($tags) || empty($tags)) {
                $tag_list = D('tag')->get_tags_by_title($data['intro']);
            } else {
                $tag_list = explode(' ', $tags);
            }
            if ($tag_list) {
                $item_tag_arr = $tag_cache = array();
                $tag_mod = M('tag');
                foreach ($tag_list as $_tag_name) {
                    $tag_id = $tag_mod->where(array('name'=>$_tag_name))->getField('id');
                    !$tag_id && $tag_id = $tag_mod->add(array('name' => $_tag_name)); //标签入库
                    $item_tag_arr[] = array('item_id'=>$item_id, 'tag_id'=>$tag_id);
                    $tag_cache[$tag_id] = $_tag_name;
                }
                if ($item_tag_arr) {
                    $item_tag = M('item_tag');
                    //清除关系
                    $item_tag->where(array('item_id'=>$item_id))->delete();
                    //商品标签关联
                    $item_tag->addAll($item_tag_arr);
                    $data['tag_cache'] = serialize($tag_cache);
                }
            }

            //更新商品
            $this->_mod->where(array('id'=>$item_id))->save($data);
            //更新图片和相册
            $item_imgs && M('item_img')->addAll($item_imgs);

            //附加属性
            $attr = $this->_post('attr', ',');
            if( $attr ){
                foreach( $attr['name'] as $key=>$val ){
                    if( $val&&$attr['value'][$key] ){
                        $atr['item_id'] = $item_id;
                        $atr['attr_name'] = $val;
                        $atr['attr_value'] = $attr['value'][$key];
                        M('item_attr')->add($atr);
                    }
                }
            }
            $this->success(L('operation_success'));
        } else {
            $id = $this->_get('id','intval');
            $item = $this->_mod->where(array('id'=>$id))->find();
            //分类
            $spid = $this->_cate_mod->where(array('id'=>$item['cate_id']))->getField('spid');
            if( $spid==0 ){
                $spid = $item['cate_id'];
            }else{
                $spid .= $item['cate_id'];
            }
            $this->assign('selected_ids',$spid); //分类选中
            $tag_cache = unserialize($item['tag_cache']);
            $item['tags'] = implode(' ', $tag_cache);
            
            $sizestr = $item["size"];
            $sizearr = explode("|",$sizestr);
            $this->assign("sizearr",$sizearr);
            
            $colorstr = $item["color"];
            $colorarr = explode("|",$colorstr);
        	foreach ($colorarr as $color){
               $this->assign("colorarr".$color,$color);
            }
            
            $this->assign('info', $item);
           
            //相册
            $img_list = M('item_img')->where(array('item_id'=>$id))->select();
            $this->assign('img_list', $img_list);
           
            $this->display();
        }
    }

    function delete_album() {
        $album_mod = M('item_img');
        $album_id = $this->_get('album_id','intval');
        $album_img = $album_mod->where('id='.$album_id)->getField('url');
        if( $album_img ){
            $ext = array_pop(explode('.', $album_img));
            $album_min_img = C('pin_attach_path') . 'item/' . str_replace('.' . $ext, '_s.' . $ext, $album_img);
            is_file($album_min_img) && @unlink($album_min_img);
            $album_img = C('pin_attach_path') . 'item/' . $album_img;
            is_file($album_img) && @unlink($album_img);
            $album_mod->delete($album_id);
        }
        echo '1';
        exit;
    }

    function delete_attr() {
        $attr_mod = M('item_attr');
        $attr_id = $this->_get('attr_id','intval');
        $attr_mod->delete($attr_id);
        echo '1';
        exit;
    }

    /**
     * 商品审核
     */
    public function check() {
        //分类信息
        $res = $this->_cate_mod->field('id,name')->select();
        $cate_list = array();
        foreach ($res as $val) {
            $cate_list[$val['id']] = $val['name'];
        }
        $this->assign('cate_list', $cate_list);
        //商品信息
        //$map = $this->_search();
        $map=array();
        $map['status']=0;
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $cate_id = $this->_request('cate_id', 'intval');
        if ($cate_id) {
            $id_arr = $this->_cate_mod->get_child_ids($cate_id, true);
            $map['cate_id'] = array('IN', $id_arr);
            $spid = $this->_cate_mod->where(array('id'=>$cate_id))->getField('spid');
            if( $spid==0 ){
                $spid = $cate_id;
            }else{
                $spid .= $cate_id;
            }
        }
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%'.$keyword.'%');
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'selected_ids' => $spid,
            'cate_id' => $cate_id,
            'keyword' => $keyword,
        ));
        //分页
        $count = $this->_mod->where($map)->count('id');
        $pager = new Page($count, 20);
        $select = $this->_mod->field('id,title,img,tag_cache,cate_id,uid,uname')->where($map)->order('id DESC');
        $select->limit($pager->firstRow.','.$pager->listRows);
        $page = $pager->show();
        $this->assign("page", $page);
        $list = $select->select();
        foreach ($list as $key=>$val) {
            $tag_list = unserialize($val['tag_cache']);
            $val['tags'] = implode(' ', $tag_list);
            $list[$key] = $val;
        }
        $this->assign('list', $list);
        $this->assign('list_table', true);
        $this->display();
    }

    /**
     * 审核操作
     */
    public function do_check() {
        $mod = D($this->_name);
        $pk = $mod->getPk();
        $ids = trim($this->_request($pk), ',');
        $datas['id']=array('in',$ids);
        $datas['status']=1;
        if ($datas) {
            if (false !== $mod->save($datas)) {
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
            }
        } else {
            IS_AJAX && $this->ajaxReturn(0, L('illegal_parameters'));
        }

    }

    /**
     * ajax获取标签
     */
    public function ajax_gettags() {
        $title = $this->_get('title', 'trim');
        $tag_list = D('tag')->get_tags_by_title($title);
        $tags = implode(' ', $tag_list);
        $this->ajaxReturn(1, L('operation_success'), $tags);
    }

    public function delete_search() {
        $items_mod = D('item');
        $items_cate_mod = D('item_cate');
        $items_likes_mod = D('item_like');
        $items_pics_mod = D('item_img');
        $items_tags_mod = D('item_tag');
        $items_comments_mod = D('item_comment');

        if (isset($_REQUEST['dosubmit'])) {
            if ($_REQUEST['isok'] == "1") {
                //搜索
                $where = '1=1';
                $keyword = trim($_POST['keyword']);
                $cate_id = trim($_POST['cate_id']);
                $cate_id = trim($_POST['cate_id']);
                $time_start = trim($_POST['time_start']);
                $time_end = trim($_POST['time_end']);
                $status = trim($_POST['status']);
                $min_price = trim($_POST['min_price']);
                $max_price = trim($_POST['max_price']);
                $min_rates = trim($_POST['min_rates']);
                $max_rates = trim($_POST['max_rates']);

                if ($keyword != '') {
                    $where .= " AND title LIKE '%" . $keyword . "%'";
                }
                if ($cate_id != ''&&$cate_id!=0) {
                    $where .= " AND cate_id=" . $cate_id;
                }
                if ($time_start != '') {
                    $time_start_int = strtotime($time_start);
                    $where .= " AND add_time>='" . $time_start_int . "'";
                }
                if ($time_end != '') {
                    $time_end_int = strtotime($time_end);
                    $where .= " AND add_time<='" . $time_end_int . "'";
                }
                if ($status != '') {
                    $where .= " AND status=" . $status;
                }
                if ($min_price != '') {
                    $where .= " AND price>=" . $min_price;
                }
                if ($max_price != '') {
                    $where .= " AND price<=" . $max_price;
                }
                if ($min_rates != '') {
                    $where .= " AND rates>=" . $min_rates;
                }
                if ($max_rates != '') {
                    $where .= " AND rates<=" . $max_rates;
                }
                $ids_list = $items_mod->where($where)->select();
                $ids = "";
                foreach ($ids_list as $val) {
                    $ids .= $val['id'] . ",";
                }
                if ($ids != "") {
                    $ids = substr($ids, 0, -1);
                    $items_likes_mod->where("item_id in(" . $ids . ")")->delete();
                    $items_pics_mod->where("item_id in(" . $ids . ")")->delete();
                    $items_tags_mod->where("item_id in(" . $ids . ")")->delete();
                    $items_comments_mod->where("item_id in(" . $ids . ")")->delete();
                    M('album_item')->where("item_id in(" . $ids . ")")->delete();
                    M('item_attr')->where("item_id in(" . $ids . ")")->delete();

                }
                $items_mod->where($where)->delete();

                //更新商品分类的数量
                $items_nums = $items_mod->field('cate_id,count(id) as items')->group('cate_id')->select();
                foreach ($items_nums as $val) {
                    $items_cate_mod->save(array('id' => $val['cate_id'], 'items' => $val['items']));
                    M('album')->save(array('cate_id' => $val['cate_id'], 'items' => $val['items']));
                }

                $this->success('删除成功', U('item/delete_search'));
            } else {
                $this->success('确认是否要删除？', U('item/delete_search'));
            }
        } else {
            $res = $this->_cate_mod->field('id,name')->select();

            $cate_list = array();
            foreach ($res as $val) {
                $cate_list[$val['id']] = $val['name'];
            }
            //$this->assign('cate_list', $cate_list);
            $this->display();
        }
    }
}