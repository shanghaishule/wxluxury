<?php

class StatisticsAction extends BackAction
{	
	public function _initialize() {
        $account_status = array(
        		0=>'已提现',
        		1=>'审核中',
        		
        );
        $this->assign('account_status',$account_status);
        
        $this->_mod_setting = M('atixian');
        $this->_mod_setting = D('account_setting');
        $this->_mod_shop = D('wecha_shop');
        
        $shopinfo = $this->_mod_shop->select();
        $this->assign('shopinfo',$shopinfo);
        
        
    }

	public function index() {
    	$map = $this->_search();
    	$this->assign("shop_tixian",$map);
    	$this->display();
    }
    
    protected function _search() {
    	$map = array();
    	
    	$shop_wecha = $this->_get("shop","trim");
    	$status = $this->_get('status',"trim");
    	$this->assign("status_m",100);
    	
    	$str_status = "";
    	if ($status != "") {
    		$str_status = " and t.status=".$status;
    		$this->assign("status_m",$status);
    	}
    	$str_shop = "";
    	if($shop_wecha != ""){
    		$str_shop = " and w.id=".$shop_wecha;
    		$this->assign("shop_id",$shop_wecha);
    	}

        $new_map = new Model();
       
        $sql = "SELECT *, w.id AS shop_id FROM tp_account_setting a, tp_wecha_shop w, tp_atixian t
				WHERE a.tokenTall = t.tokenTall
				AND w.tokenTall = a.tokenTall".$str_status.$str_shop;
       
        $map = $new_map->query($sql);
    	return $map;
    }
    
    public function edit() {
    	$id = $this->_get('id','intval');
    	//$account_master = $this->_mod_bill_mst->where(array('id'=>$id))->find();
    	
    	$modelmst = new Model();
    	/*
    	$account_master = $modelmst->table("tp_account_bill_mst m, tp_account_setting c")
    	->where("m.tokenTall=c.tokenTall and m.id='".$id."'")
    	->field("m.*, c.bankname, c.account, c.payee, c.mobile")
    	->find();
    	*/
    	
    	$account_master = $modelmst->table('tp_account_bill_mst m')->join('tp_account_setting c on m.tokenTall=c.tokenTall')->where("m.id='".$id."'")->field('m.*, c.bankname, c.account, c.payee, c.mobile')->find();
    	
    	$model=new Model();
    	$account_detail=$model->table("tp_account_bill_dtl m, tp_item_order c")
    	->where("m.orderId=c.orderId and m.billnum='".$account_master['billnum']."'")
    	->field("m.*, c.add_time, c.status")
    	->select();
    	
    	//$account_detail = $this->_mod_bill_dtl->where('billnum='.$account_master['billnum'])->select();
    	$this->assign('account_detail',$account_detail);
    	$this->assign('account_master', $account_master);
    	$this->display();
    }

    public function status()
    {
    	//dump($_SESSION);exit;
    	
    	$id= $this->_get('id', 'trim');
    	!$id && $this->_404();
    	$status= $this->_get('status', 'trim');
    	!$status && $this->_404();
    	
    	$before_status_arr = $this->_mod_bill_mst->field('status')->where('id='.$id)->select();
    	$before_status = $before_status_arr[0]['status'];
    	
    	if($status == 'mall_confirm'){
    		if($before_status == '0' || $before_status == '2' ){
    			if($before_status == '0')
    				$data['status']='1';
    			if($before_status == '2')
    				$data['status']='3';

    			$data['duizhang']=$_SESSION['username'];
    			$data['duizhang_time']=time();
    	   
    			if($this->_mod_bill_mst->where('id='.$id)->save($data))
    			{
    				$this->success('操作成功!');
    			}else{
    				$this->error('操作失败!');
    			}
    		}else{
    			$this->error('前置状态错误!');
    		}
    	}elseif ($status == 'haspay'){
    		if($before_status == '3'){
    			$data['status']='4';
    		
    			$data['pay']=$_SESSION['username'];
    			$data['pay_time']=time();
    		
    			if($this->_mod_bill_mst->where('id='.$id)->save($data))
    			{
    				$this->success('操作成功!');
    			}else{
    				$this->error('操作失败!');
    			}
    		}else{
    			$this->error('前置状态错误!');
    		}
    	}
    
    
    }
    
    public function hadti()
    {
    	$ids = trim($this->_request('tokenTall'), ',');
    	if ($ids) {
    		$tixian = M("atixian")->where("tokenTall='".$ids."'")->find();
    		if ($tixian['status'] == 1) {
    			$this->error('已经提现，不能设置！');
    		}else{
    			//需要删除主从表
    			$dtldel["hadti"] = $tixian["hadti"] + $tixian["yaoti"];
    			$dtldel["yaoti"] = 0;
    			$dtldel["status"] = 1;
    			$dtlmst =M("atixian")->where("tokenTall='".$ids."'")->save($dtldel);

	    		if (false !== $dtldel ) {
	    			$this->success('设置提现成功');
	    		} else {
	    			$this->error('设置提现失败');
	    		}
    		}
    	} else {
    		$this->error('参数错误');
    	}
    }
    //导出体现
	public function export(){
		$exportArr = $this->_search();
		dump($exportArr);die();
		exportexcel($exportArr,array('商户流水号','收款人email','收款人姓名','付款金额(元)','付款理由'),'商家账号');
	}
}
?>