<?php

class promotionAction extends backendAction {
	
	public function _initialize() {
		parent::_initialize();
		$this->_mod = D('set_discount');
		$this->_cate_mod = D('item_cate');
		$brandlist= $this->_brand=M('brandlist')->where('status=1')->order('ordid asc,id asc')->select();
		$this->assign('brandlist',$brandlist);
	}
	
	
	public function index() {
	
		$map = array();
		$UserDB = D('set_promotion');
		$keyword = $_GET['keyword'];
		if($keyword != ""){
			$map['theme'] = $keyword;
			$this->assign("keyword",$keyword);
		}
		
		$_SESSION['tokenTall'] = $_GET['tokenTall'];

		$count = $UserDB->where($map)->count();
		$Page       = new Page($count,8);// 实例化分页类 传入总记录数
		// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
		$nowPage = isset($_GET['p'])?$_GET['p']:1;
		$show       = $Page->show();// 分页显示输出
		$list2 = $UserDB->where($map)->order('id ASC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list2 as $content){
			if(strlen($content["theme"]) > 60){
				$content["theme"] = mb_substr($content["theme"], 0,30,"utf-8")."...";
			}
			$list[] = $content;
		}
		//var_dump($list)		;die();
		$brand = M("brandlist")->select();
		
		$this->assign('list',$list);
		$this->assign("brand",$brand);
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('tokenTall',$_SESSION['tokenTall']);// 赋值分页输出
		$this->display();
	}
	
	public function deletebrand()
	{
	
		$mod = M("set_discount");
	
		$pk = $mod->getPk();
		$ids = trim($this->_request($pk), ',');
	
		 
		if ($ids) {
			 
			 
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
?>