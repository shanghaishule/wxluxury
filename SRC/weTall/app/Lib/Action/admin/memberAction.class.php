<?php
class memberAction extends backendAction{
	public function _initialize(){
		parent::_initialize();
		$this->_mod = D('user_info');
	}
	public function _before_index() {
		$big_menu = array(
				'title' => '查看会员详细信息',
				'iframe' => U('member/edit'),
				'id' => 'edit',
				'width' => '400',
				'height' => '130',
		);
	}	
	//会员详情
	public function _before_edit(){
		dump('123');exit;
	}
	//会员详情
	public function _before_add(){
	
	}	
}
?>