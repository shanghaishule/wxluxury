<?php
class OrderListAction extends BackAction{
	public function _initialize(){
		$order_status=array(1=>'待付款',2=>'待发货',3=>'待收货',4=>'完成',5=>'关闭');
		$this->assign('order_status',$order_status);
	}
	public function index(){
		if(IS_GET){
			$status = $this->_get('status','trim');
			$shop = $this->_get('shop','trim');
			if($status != ''){
				$where['status']=$status;
			}
			if($shop != ''){
				$where['tokenTall']=$shop;
			}
			
		}else{
			$where = '';
		}
		$item_order = M('item_order');
		$count = $item_order->where($where)->count();
		$Page = new Page($count,10);
		$nowPage = isset($_GET['p'])?$_GET['p']:1;
		$show       = $Page->show();// 分页显示输出
		$pageData = $item_order->where($where)->order('id ASC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$shopArr = array();
		foreach($pageData as $key => $val){
			$shopName = M('wecha_shop')->where(array("tokenTall"=>$val['tokenTall']))->find();
			if(!empty($shopName)){
				$pageData[$key]['shopName']=$shopName['name'];
				$shopArr[]=$shopName;
			}

		}
		$this->assign('shopArr',$shopArr);
		$this->assign('list',$pageData);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
}
?>