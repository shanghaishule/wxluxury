<?php

class upload_shopAction extends backendAction
{

    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('upload_shop');
    }

    public function _before_index() {
        $big_menu = array(
            'title' => '添加品牌',
            'iframe' => U('upload_shop/add'),
            'id' => 'add',
            'width' => '400',
            'height' => '130'
        );
        
        //$this->assign('big_menu', $big_menu);
        //sort($big_menu);
        //默认排序
        //$this->sort = 'ordid';
       //$this->order = 'ASC';
       // $this->sort="name";
        //$this->order ="asc";
        
    }           
    
    
    public function _before_add(){
    	/*if (IS_POST) {
    		//上传图片
    		if (empty($_FILES['img']['name'])) {
    			$_SESSION["img_name"] = "";
    			$this->error('请上传品牌图片');
    		}
    		else{
    			$date_dir = date('ym/d/'); //上传目录
    			$item_imgs = array(); //相册
    			$result = $this->_upload($_FILES['img'], 'item/'.$date_dir, array(
    					'width'=>C('pin_item_bimg.width').','.C('pin_item_img.width').','.C('pin_item_simg.width'),
    					'height'=>C('pin_item_bimg.height').','.C('pin_item_img.height').','.C('pin_item_simg.height'),
    					'suffix' => '_b',
    					//'remove_origin'=>true
    			));
    			$_SESSION["im_name"] = $result['info'][0]['savename'];
    			if ($result['error']) {
    				$this->error($result['info']);
    			} else {
    				$_SESSION["img_name"] = $result['info'][0]['savename'];
    			}
    		}
    		
    		//图片2
    		if (empty($_FILES['imgurl']['name'])) {
    			$_SESSION["img_name2"] = "";
    			$this->error('请上传品牌图片2');
    		}
    		else{
    			$date_dir = date('ym/d/'); //上传目录
    			$item_imgs = array(); //相册
    			$result2 = $this->_upload($_FILES['imgurl'], 'item/'.$date_dir, array(
    					'width'=>C('pin_item_bimg.width').','.C('pin_item_img.width').','.C('pin_item_simg.width'),
    					'height'=>C('pin_item_bimg.height').','.C('pin_item_img.height').','.C('pin_item_simg.height'),
    					'suffix' => '_b',
    					//'remove_origin'=>true
    			));
    			$_SESSION["im_name2"] = $result2['info'][0]['savename'];
    			if ($result2['error']) {
    				$this->error($result2['info']);
    			} else {
    				$_SESSION["img_name2"] = $result2['info'][0]['savename'];
    			}
    		}
    	}*/
    }
    public function _before_update($data){
    	//$this->ajaxReturn(0, $data['show_addr']);
    	$result = $this->getlatlng($data['show_addr']);
    	$data["longitude"] = $result['long'];
    	$data["lat"] = $result['lat'];
    	return $data;
    }
    public function _before_insert($data) {
    	$data['tokenTall'] = $this->getTokenTall();   
    	$result = $this->getlatlng($data['show_addr']);
    	$data["longitude"] = $result['long'];
    	$data["lat"] = $result['lat'];
    	return $data;
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
    
    protected function _search() {
        $map = array();
        $map['tokenTall'] = $this->getTokenTall();
        ($keyword = $this->_request('keyword', 'trim')) && $map['name'] = array('like', '%'.$keyword.'%');
        $this->assign('search', array(
            'keyword' => $keyword,
        ));
        return $map;
    }

    public function ajax_check_name() {
        $name = $this->_get('name', 'trim');
        $id = $this->_get('id', 'intval');
        if (D('score_item_cate')->name_exists($name, $id)) {
            $this->ajaxReturn(0, L('该分类名称已存在'));
        } else {
            $this->ajaxReturn(1);
        }
    }
    
      public function deletebrand()
    {
    	 
        $mod = D($this->_name);
      
        $pk = $mod->getPk();
        $ids = trim($this->_request($pk), ',');
        
       
        if ($ids) {
        	$count=M('item')->where("brand in (".$ids.")")->count();
        	if($count>0)
        	{
          IS_AJAX && $this->ajaxReturn(0,'品牌被引用，不能删除');exit;
        	}
        	
            if (false !== $mod->delete($ids)) {
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'));
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            IS_AJAX && $this->ajaxReturn(0, L('illegal_parameters'));
            $this->error(L('illegal_parameters'));
        }
    }
    
}