<?php

class DatafromTbAction extends BackAction
{

    public function index() {
        $map = array();
		$UserDB = D('item_taobao');
		$brandlist = M("brandlist");
		//if (IS_POST){
		$huohao = $this->_request("huohao","trim");
		if ($huohao) {
			$map["Huohao"] = array('like', '%'.$huohao.'%');
		}
		$brand = $this->_request("brand","trim");
		if ($brand) {
			$where["name"]=array('like', '%'.$brand.'%');
			$brandid=M("brandlist")->where($where)->find();
			$map["brand"] = $brandid["id"];
		}
		$time_start = $this->_request('time_start', 'trim');
    	$time_end = $this->_request('time_end', 'trim');
    	if($time_start && $time_end){
    	    $map['add_time'] = array('between', array(strtotime($time_start), strtotime($time_end)+(24*60*60-1)));
    	} else if($time_start) {
    	    $map['add_time'] = array('egt', strtotime($time_start));
    	} else if($time_end) {
    	    $map['add_time'] = array('elt', strtotime($time_end)+(24*60*60-1));
    	}
		//}
		$count = $UserDB->where($map)->count();
		$Page       = new Page($count,100);// 实例化分页类 传入总记录数
		// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
		$nowPage = isset($_GET['p'])?$_GET['p']:1;
		$show       = $Page->show();// 分页显示输出
		$list = $UserDB->where($map)->order('huohao ASC')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$this->assign("im_message",M("message_check")->field("text")->find());
		$this->assign("brandlist",$brandlist->order('name')->select());
		$this->assign('list',$list);
		$this->assign('page',$show);// 赋值分页输出
		
		$this->assign('search', array(
				'huohao' => $huohao,
				'brand'=> $brand,
				'time_start'=>$time_start,
				'time_end'=>$time_end
		));
		
		$this->display();
       
    }
    
    public function delete(){
    	$Item = M('item_taobao');
    	
    	$ids = trim($this->_request(id), ',');
    	if (false !== $Item->delete($ids)){
    		//IS_AJAX && $this->ajaxReturn(1, L('operation_success'));
    		$this->success(L('operation_success'));
    	}else{
    		 
    		//IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
    		$this->error(L('operation_failure'));
    	}
    }

    protected function _search() {
        $map = array();
        ($keyword = $this->_request('keyword', 'trim')) && $map['name'] = array('like', '%'.$keyword.'%');
        $this->assign('search', array(
            'keyword' => $keyword,
        	'name'=> $map['name']
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
    public function deletemessage()
    {
    	$datamessage["text"]="";
    	$message = M("message_check");
    	if($message->where("1 = 1")->save($datamessage)){
    		$this->success("message被清空！");
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
?>