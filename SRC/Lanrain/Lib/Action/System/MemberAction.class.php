<?php

class MemberAction extends BackAction
{	
	public $_mod_;
	public function _initialize() {
        $this->_mod_ = M('user');
    }

	public function index() {
    	$mod = $this->_mod_;
    	//dump($mod);exit;
    	$this->_list($mod);
    	$this->display();
    }
    //会员详情
   public function detailInfo(){
   	   $where['id'] = $this->_get('id','trim');
   	   $userInfo = M('user_info')->where($where)->find();
   	   if(empty($userInfo)){
   	   	   $this->error("该用户还未填详细信息");
   	   }else{
   	   	   $this->assign('userInfo',$userInfo);
   	   	   dump($userInfo);die();
   	   	   $this->display();
   	   }
   }
    
   public function delete(){//会员管理删除会员
   	$mod =M('user');
   	$ids = trim($this->_request('id'), ',');
   	if($ids){
   		if (false !== $mod->delete($ids)) {
   			$this->success("删除成功");
   		}else{
   			$this->error("删除失败");
   		}
   	}else{
   		$this->error("没有该用户");
   	}
   	
   }

}
?>