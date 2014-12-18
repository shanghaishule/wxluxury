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
    	$this->_list($mod,array(),'','','*',100);
    	$this->display();
    }
    //会员详情
   public function edit(){
   	   $where['uid'] = $this->_get('id','trim');
   	   $userInfo = M('user_info')->where($where)->find();
   	   $userB = $this->_mod_->where($where)->find();
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
    	//导出信息
   public function export(){
   	       $userInfo = $this->_mod_ ->field("nickname,province,city")->select();
   	      
   	       foreach ($userInfo as $key => $val){
   	         	 $res = M('user_info')->field("sex,birthday,mail,height,weight,yifu_size,kuzi_size,xie_size,hobby_title,hobby_color,hobby_style,hobby_element")->where(array('uid'=>$val['uid']))->find();
   	         	 $userInfo[$key][] = $res;
   	       }
    		exportexcel($userInfo,array('会员名称','省份','城市','性别','生日','邮箱','身高','体重','邮箱','衣服尺寸','裤子尺寸','主题偏好','颜色偏好','风格偏好','元素偏好'),'申请开店信息');
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