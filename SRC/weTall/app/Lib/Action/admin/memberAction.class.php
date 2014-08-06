<?php
class memberAction extends backendAction{
	public function _initialize(){
		parent::_initialize();
		$this->_mod = D('user_info');
	}
	public function index() {
	}	
	//会员详情
	public function edit(){
		$where['uid'] = $this->_get('id','trim');
		$userInfo = $this->_mod->where($where)->find();
		$this->assign('open_validator', true);
		$this->assign('userInfo',$userInfo);
		if (IS_AJAX) {
			$response = $this->fetch();
			$this->ajaxReturn(1, '', $response);
		} else {
			$this->display();
		}
	}
	//会员详情
	public function _before_add(){
	
	}	
}
?>