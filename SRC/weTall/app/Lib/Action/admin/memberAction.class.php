<?php
class memberAction extends backendAction{
	public function _initialize(){
		parent::_initialize();
		$this->_mod=D('user_info');
	}
	public function _before_index() {
		$big_menu = array(
				'title' => '详细资料',
				'iframe' => U('member/edit'),
				'id' => 'edit',
				'width' => '400',
				'height' => '130'
		);
	}	
	public function edit(){
		$where['uid'] = $this->_get('id','trim',0);
		$userInfo = $this->_mod->where($where)->find();
		$userB = M('user')->where($where)->find();
		$this->assign('open_validator', true);
		$this->assign('userInfo',$userInfo);
		$this->assign('userB',$userB);
		if (IS_AJAX) {
			$response = $this->fetch();
			$this->ajaxReturn(1, '', $response);
		} else {
			$this->display();
		}
	}
}
?>