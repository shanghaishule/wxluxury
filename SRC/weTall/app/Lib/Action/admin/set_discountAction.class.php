<?php

class set_discountAction extends backendAction {

	public function _initialize() {
		parent::_initialize();
		$this->_mod = D('set_discount');
		$this->_cate_mod = D('item_cate');
		$brandlist= $this->_brand=M('brandlist')->where('status=1')->order('ordid asc,id asc')->select();
		$this->assign('brandlist',$brandlist);
	}
	
	public function _before_index() {
		
		$tokenTall = $this->getTokenTall();
		$this->assign('tokenTall',$tokenTall);
		//显示模式
		$sm = $this->_get('sm', 'trim');
		$this->assign('sm', $sm);
	
		$big_menu = array(
				'title' => '添加促销活动',
				'iframe' => U('set_discount/add',array('tokenTall'=>$tokenTall)),
				'id' => 'add',
				'width' => '520',
				'height' => '410',
		);
		$this->assign('big_menu', $big_menu);
		
		//分类信息
		$res = $this->_cate_mod->field('id,name')->select();
		 
		$cate_list = array();
		foreach ($res as $val) {
			$cate_list[$val['id']] = $val['name'];
		}
	
		$this->assign('cate_list', $cate_list);
	
		//默认排序
		$this->sort = 'id ASC,';
		$this->order ='start_time DESC';
	}
	
	public function _before_add(){
		$brand = M("brandlist");
		$this->assign("brand",$brand->select());
		if (IS_POST) {
		//上传图片
			if (empty($_FILES['img']['name'])) {
				$_SESSION["img_name"] = "";
				$this->error('请上传商品图片');
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
	            $_SESSION["img_name"] = $result['info'][0]['savename'];
	            if ($result['error']) {
	                $this->error($result['info']);
	            } else {
	            	$_SESSION["img_name"] = $result['info'][0]['savename'];
	            }
			}
		}
		
	}
	public function _before_edit(){
		$brand = M("brandlist");
		$this->assign("brand",$brand->select());
		if (IS_POST) {
			//上传图片
			if (!empty($_FILES['img']['name'])) {
				$date_dir = date('ym/d/'); //上传目录
				$item_imgs = array(); //相册
				$result = $this->_upload($_FILES['img'], 'item/'.$date_dir, array(
						'width'=>C('pin_item_bimg.width').','.C('pin_item_img.width').','.C('pin_item_simg.width'),
						'height'=>C('pin_item_bimg.height').','.C('pin_item_img.height').','.C('pin_item_simg.height'),
						'suffix' => '_b',
						//'remove_origin'=>true
				));
				$_SESSION["img_name"] = $result['info'][0]['savename'];
				if ($result['error']) {
					$this->error($result['info']);
				} else {
					$_SESSION["img_name"] = $result['info'][0]['savename'];
				}
				
			}else{						
				$_SESSION["img_name"] = "";
			}
		}	
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
	
	
	function delete_album() {
		$album_mod = M('item');
		$album_id = $this->_get('album_id','trim');
		$item_id = $this->_get('item_id','trim');//echo $item_id."hi";die();
		$where["id"] = $item_id;
	
		$album_img = $album_mod->where($where)->find();
		if($album_img ){
			$data["images"] = preg_replace('/|'.$album_id.'/',"",$album_img["images"]);
			 
			$album_mod->where($where)->save($data);
		}
		echo '1';
		exit;
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
	/*
	 * 设置活动开始
	 */
	public function start(){
		$id = $this->_get("id","trim");
		$where["id"] = $id;
		$set_discount = M("set_discount");
		$status = $set_discount->where($where)->find();
		if ($status["status"] == "2") {
			$message = "活动已经结束";
		}elseif ($status["status"] == "1"){
			$message = "活动已经开始";
		}else {
			$data["status"] = 1;
			if ($set_discount->where($where)->save($data)) {
				$message = "设置活动开始成功";;
			}
		}
		$this->ajaxReturn(1, L('operation_success'), $tags);
		$this->success($message);
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