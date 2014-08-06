<?php
class OrderListAction extends BackAction{
	public function index(){
		$item_order = M('item_order');
		$count = $item_order->count();
		$Page = new Page($count,10);
		$nowPage = isset($_GET['p'])?$_GET['p']:1;
		$show       = $Page->show();// 分页显示输出
		$pageData = $item_order->order('id ASC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$pageData);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
}
?>