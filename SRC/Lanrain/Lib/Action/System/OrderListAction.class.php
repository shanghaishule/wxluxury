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
		$pageData = $item_order->where($where)->order('add_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$shopArr = M('wecha_shop')->field("tokenTall,name")->select();
		foreach($pageData as $key => $val){
			$shopName = M('wecha_shop')->where(array("tokenTall"=>$val['tokenTall']))->find();
			$brand = M('brandlist')->where(array('id'=>$shopName['BelongBrand']))->getField('name');
			if(!empty($shopName)){
				$pageData[$key]['shopName']=$shopName['name'];
				$pageData[$key]['brand'] = $brand;
			}
		}
		$this->assign('shopArr',$shopArr);
		$this->assign('list',$pageData);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
	
	//订单导出
	public function export(){
		$item_order = M('item_order');
		$pageData = $item_order->field("orderId,status,order_sumPrice,address_name,userName,mobile,address,supportmetho,freetype,add_time,support_time")->order('add_time DESC')->select();
		foreach($pageData as $key => $val){
			$shopName = M('wecha_shop')->where(array("tokenTall"=>$val['tokenTall']))->find();
			$brand = M('brandlist')->where(array('id'=>$shopName['BelongBrand']))->getField('name');
			 if($val['status'] == 1){
			 	$pageData[$key]['status'] ="待付款";
			 }elseif ($val['status'] == 2){
			 	$pageData[$key]['status'] ="待发货";
			 }elseif($val['status'] ==3){
			 	$pageData[$key]['status'] ="待收货";
			 }else{
			 	$pageData[$key]['status'] ="完成";
			 }
			 
			 if($val['supportmetho'] == 1){
			 	 $pageData[$key]['supportmetho'] = "支付宝支付";
			 }elseif($val['supportmetho'] ==2 || $val['supportmetho'] == ''){
			 	 $pageData[$key]['supportmetho'] = "货到付款";
			 }elseif($val['supportmetho'] == 3){
			 	$pageData[$key]['supportmetho'] = "银联支付";
			 }else{
			 	$pageData[$key]['supportmetho'] = "微信支付";
			 }
			 
			 if($val['freetype'] == 0){
			 	 $pageData[$key]['freetype'] ="卖家包邮";
			 }elseif($val['freetype'] == 1){
			 	 $pageData[$key]['freetype'] ="平邮";
			 }elseif($val['freetype'] == 2){
			 	 $pageData[$key]['freetype'] ="快递";
			 }else{
			 	$pageData[$key]['freetype'] ="ems";
			 }
			 $pageData[$key]['add_time'] = date( "Y-m-d H:i:s",$val['add_time']);
			 $pageData[$key]['support_time'] = date_format($val['support_time'], "Y-m-d H:i:s");
			if(!empty($shopName)){
				$pageData[$key]['shopName']=$shopName['name'];
				$pageData[$key]['brand'] = $brand;
			}
		}	
		exportexcel($pageData,array('订单号','状态','订单金额','收货人','用户昵称','联系电话','收货地址','支付方式','配送','下单时间','支付时间'),'商品订单');
	}
}
?>