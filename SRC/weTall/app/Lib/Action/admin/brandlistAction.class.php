<?php

class brandlistAction extends backendAction
{

    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('brandlist');
    }

    public function _before_index() {
        $big_menu = array(
            'title' => '添加品牌',
            'iframe' => U('brandlist/add'),
            'id' => 'add',
            'width' => '400',
            'height' => '130'
        );
        //$this->assign('big_menu', $big_menu);

        //默认排序
        //$this->sort = 'ordid';
       // $this->order = 'ASC';
        
        sort($big_menu);
        foreach($big_menu as $key => $val){
        	echo "big_menu[".$key."] = " . $val . "\n";
        	
        }
    }
         
        
    }

    public function _before_add(){
    	if (IS_POST) {
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
    	}
    }
    public function _before_edit(){
    	if (IS_POST) {
    		//上传图片
    		if (empty($_FILES['img']['name'])) {
    			$_SESSION["img_name"] = "";
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
    			$_SESSION["img_edit_name"] = $result['info'][0]['savename'];
    			if ($result['error']) {
    				$this->error($result['info']);
    			} else {
    				$_SESSION["img_edit_name"] = $result['info'][0]['savename'];
    			}
    		}
    		
    		//图片2
    		if (empty($_FILES['imgurl']['name'])) {
    			$_SESSION["img_edit_name2"] = "";
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
    			$_SESSION["img_edit_name2"] = $result2['info'][0]['savename'];
    			if ($result2['error']) {
    				$this->error($result2['info']);
    			} else {
    				$_SESSION["img_edit_name2"] = $result2['info'][0]['savename'];
    			}
    		}
    	}
    }
    public function _before_insert($data) {
    	$data['tokenTall'] = $this->getTokenTall();    	
    	return $data;
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