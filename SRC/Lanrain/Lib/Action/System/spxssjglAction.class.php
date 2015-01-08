<?php

class spxssjglAction extends BackAction
{	
	public function index() {
    	$map = $this->_search();
    	//dump($map);exit;
    	$where = "";
    	$map['start_time'] && $where .= ' and a.add_time >= '.$map['start_time'];
    	$map['end_time'] && $where .= ' and a.add_time <= '.$map['end_time'];
    	$map['shop'] && $where .= ' and c.name like "%'.$map['shop'].'%"';
    	$map['Huohao'] && $where .= ' and d.Huohao like "%'.$map['Huohao'].'%"';
    	$map['title'] && $where .= ' and b.title like "%'.$map['title'].'%"';
    	
    	$Model = new Model();
    	$sql = 'SELECT c.name shop, d.Huohao, b.title, b.img, sum(b.quantity) qty, sum(b.quantity * b.price) total '
		.' FROM `tp_item_order` a, `tp_order_detail` b, `tp_wecha_shop` c, `tp_item` d '
		.' WHERE a.orderId = b.orderId and a.tokenTall = c.tokenTall and b.itemId = d.id and a.status = 4 '.$where
		.' GROUP BY c.name, b.itemId, b.title, b.img '
		.' ORDER BY a.add_time; ';
    	$List = $Model->query($sql);
    	foreach($List as $key => $val){
    		 $res = M('brandlist')->where(array('id'=>$val['BelongBrand']))->getField('name');
    		 $List[$key]['brand'] = $res;
    	}
    	dump($List);exit;
		$this->assign('list',$List);
		
		$sumqty = 0.00;
		$sumtotal = 0.00;
		foreach ($List as $one){
			$sumqty = $sumqty + $one['qty'];
			$sumtotal = $sumtotal + $one['total'];
		}
		$this->assign('sumqty',$sumqty);
		$this->assign('sumtotal',$sumtotal);
		
		$this->display();
    }
    
    protected function _search() {
    	$map = array();
    	
    	($start_time = $this->_request('start_time', 'trim')) && $start_time .= ' 00:00:00';
    	($end_time = $this->_request('end_time', 'trim')) && $end_time .= ' 23:59:59';
    	
        ($start_time) && $map['start_time'] = strtotime($start_time);
        ($end_time) && $map['end_time'] = strtotime($end_time);
        ($shop = $this->_request('shop', 'trim')) && $map['shop'] = $shop;
        ($Huohao = $this->_request('Huohao', 'trim')) && $map['Huohao'] = $Huohao;
        ($title = $this->_request('title', 'trim')) && $map['title'] = $title;
        
        $this->assign('search', array(
        	'start_time' => $start_time,
        	'end_time' => $end_time,
        	'shop' => $shop,
        	'Huohao' => $Huohao,
        	'title' => $title,
        ));
        
        
        return $map;
    }
    
}
?>