<?php
class item_rukuAction extends backendAction {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('item_taobao');
        
    }

    public function _before_index() {
        //默认排序
        $this->sort = 'ordid ASC,';
        $this->order ='add_time DESC';
    }
    
    public function index() {
    	$map = $this->_search();
        $mod = $this->_mod;
        //dump($map);exit;
    	!empty($map) && $this->_list($mod, $map);
    	$this->display();
    }

    protected function _search() {
    	$tokenTall = $this->getTokenTall();
    	$brand = M("wecha_shop")->where(array('tokenTall'=>$tokenTall))->find();
    	
        $map = array();
        ($Huohao = $this->_request('Huohao', 'trim')) && $map['Huohao'] = array('like', '%'.$Huohao.'%');
        ($brand = $brand["BelongBrand"]) && $map['brand'] = array('eq', $brand);
        
        $this->assign('search', array(
        	'brand' => $brand,
            'Huohao' => $Huohao,
        ));
        return $map;
    }

    public function ruku() {
    	$ids = $this->_get("id","trim");
    	//$this->ajaxReturn(0,$ids);
    	if ($ids) {
    		$token = $this->getTokenTall();
    		$get_num = 0; //领取成功计数器
    		$failed_num = 0;//领取失败计数器
    		$having_num = 0; //已经领取计数器
    		//实体商品
    		$items = M("item");
    		
    		$idarr = explode(',',$ids);
    		foreach ($idarr as $id){
    			$item_goods = $this->_mod->where(array('id'=>$id))->select();
    			$item_new = array();
    			foreach ($item_goods as $item_good){
    				$item_new = $item_good;
    				$item_new["tokenTall"] = $token;
    				$item_new["id"]="";
    				$item_new["old_price"] = $item_good["price"];
    				$items_having["Uninum"] = $item_good["Uninum"];
    				$items_having["tokenTall"] = $token;
    				//var_dump($item_new);die();
    				if ($items->where($items_having)->find()) {
    					$having_num ++;
    				}elseif ($items->add($item_new)) {
    					$get_num ++;
    				}else{
    					$failed_num ++;
    				}
    			}
    			
    		}
    		if ($get_num > 0) {
    			$message = "您本次成功领取".$get_num."个商品，有".$failed_num ++."个商品没有成功";
    			$this->ajaxReturn(1,$message);
    			//$this->success($message,true);
    		}else{
    			$message = "无法入库，你填写的货号不存在或者该商品已经存在您的库中！";
    			$this->ajaxReturn(0,$message);
    			//$this->error($message);
    		}
    		
    		
    	} else {
    		$this->ajaxReturn(0,"没有选择任何商品！");
    	}
    }

    public function alloneruku() {
    	$map = $this->_search();
    	$mod = $this->_mod;
    	$select = $mod->where($map)->select();
    	$ids = "";
    	foreach ($select as $oneselect){
    		$ids .= $oneselect['id'].',';
    	}
    	$ids = rtrim($ids,',');
    	//dump($ids);exit;
    	if ($ids) {
    		$token = $this->getTokenTall();
    		$get_num = 0; //领取成功计数器
    		$failed_num = 0;//领取失败计数器
    		$having_num = 0; //已经领取计数器
    		//实体商品
    		$items = M("item");
    
    		$idarr = explode(',',$ids);
    		foreach ($idarr as $id){
    			$item_goods = $this->_mod->where(array('id'=>$id))->select();
    			$item_new = array();
    			foreach ($item_goods as $item_good){
    				$item_new = $item_good;
    				$item_new["tokenTall"] = $token;
    				$item_new["id"]="";
    				$item_new["old_price"] = $item_good["price"];
    				$items_having["Uninum"] = $item_good["Uninum"];
    				$items_having["tokenTall"] = $token;
    				//var_dump($item_new);die();
    				if ($items->where($items_having)->find()) {
    					$having_num ++;
    				}elseif ($items->add($item_new)) {
    					$get_num ++;
    				}else{
    					$failed_num ++;
    				}
    			}
    			 
    		}
    		if ($get_num > 0) {
    			$message = "您本次成功领取".$get_num."个商品，有".$failed_num ++."个商品没有成功";
    			//$this->ajaxReturn(1,$message);
    			$this->success($message);
    		}else{
    			$message = "无法入库，你填写的货号不存在或者该商品已经存在您的库中！";
    			//$this->ajaxReturn(0,$message);
    			$this->error($message);
    		}
    
    
    	} else {
    		//$this->ajaxReturn(0,"没有选择任何商品！");
    		$this->error("没有选择任何商品！");
    	}
    }
    
}


