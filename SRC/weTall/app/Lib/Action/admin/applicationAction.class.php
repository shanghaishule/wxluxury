<?php
class applicationAction extends backendAction {

  
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('application');
       
    }

    public function search_user(){
    	$id = $this->_request('id', 'trim', 0);
    	$user["id"] = $id;
    	$user_data = $this->_mod->where($user)->find();
    	$this->assign("user_info",$user_data);
    	if (IS_AJAX) {
    		$response = $this->fetch();
    		$this->ajaxReturn(1, '', $response);
    	} else {
    		$this->display();
    	}
    }
}