<?php

class DatafromShopAction extends BackAction
{

    public function index() {

    	if(IS_POST){    	 
    		$return_data = $this->uploadShop();    		
    		$_SESSION['brand']=$return_data['brand'];    		
   		    $this->success($return_data['message'],"",FALSE);
   		    $_SESSION['is_reload']= TRUE;
   		    $where['shop_name']="";
    			
    	}else{
    		if($_SESSION['is_reload'] == FALSE){
    			$shop_name = $this->_get('shop_name', 'trim');
    		}
    		$_SESSION['is_reload']= FALSE;
    		$where['shop_name']= $shop_name;
    		
    		
    	}
    	    	
    	$uploadShop=M('upload_shop');
    	if($where['shop_name'] == ""){
    		$count = $uploadShop->count();
    	}else{
    		$count = $uploadShop->where($where)->count();
    	}
    	$Page       = new Page($count,8);// 实例化分页类 传入总记录数
    	// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
    	$nowPage = isset($_GET['p'])?$_GET['p']:1;
    	$show       = $Page->show();// 分页显示输出
    	
    	if($where['shop_name'] == ""){
    		$pageData = $uploadShop->order('id ASC')->limit($Page->firstRow.','.$Page->listRows)->select();
    	}else{
    		$pageData = $uploadShop->where($where)->order('id ASC')->limit($Page->firstRow.','.$Page->listRows)->select();
    		
    	}
    	$this->assign('list',$pageData);
    	$this->assign('page',$show);// 赋值分页输出
    	$this->assign('brand',$_SESSION['brand']);// 赋值分页输出
    	$this->assign('shop_name',$shop_name);// 赋值分页输出
		$this->display();
		
    }

    public function uploadShop(){    	

    	//return
    	$return_data = array();
    	//brand追加
    	$brand = $this->_request('brand', 'trim');
    	if($brand == ""){
    		$this->error("品牌名不能为空！");
    	}
    	
    	if($_FILES['upd_shop']['tmp_name'] == ""){
    		$this->error("上传文件不能为空！");
    	}
    	    	
    	$brandlist = M("brandlist");
    	$bl_data['name'] = $brand;    	
    	$is_exist = $brandlist->where($bl_data)->find();

    	if(!$is_exist){    		
    		$brandlist->add($bl_data);
    	}
    	  	
    	    	
    	//数据导入
    	$colInfo = array();
    	//读入Excel 
    	$colInfo['shop_name'] = "0"; 
    	$colInfo['show_addr'] = "1";
    	$colInfo['lbs_addr'] = "2";
    	$colInfo['phone'] = "3";
    	    	
    	$add_info['brand_name']= $bl_data['name'];    		
    	
    	$param['tmp_name'] = $_FILES['upd_shop']['tmp_name'];
    	$param['start_row'] = 1;
    	$param['col_info'] = $colInfo;
    	$param['add_info'] = $add_info;
    		
    	$data = $this->uploadExcel($param);
    	  	
    	//写入DB
    	$uploadShop=M('upload_shop');
    	$exist_data=array();
    	$success_data=array();
    	$exist_num = 0;
    	$add_num = 0;
    	
    	foreach ($data as $val){
     		$where['lbs_addr'] = $val['lbs_addr'];
     		$where['brand_name'] = $val['brand_name'];
     		
    		$is_exist = $uploadShop->where($where)->select();
 
    		if($is_exist != null){
    			$exist_data[$exist_num] = $val;
    			$exist_num++;
    		}else{
    			$uploadShop->add($val);    
    			$success_data[$add_num] = $val;
    			$add_num ++ ;	
    		}
    	}
    	
    	//显示
    	$message = "添加成功：".$add_num."条\r\n";
    	$message = $message."失败：".$exist_num."条";

    	$return_data['list'] = $success_data;
    	$return_data['message'] = $message;
    	$return_data['num'] = $add_num;
    	$return_data['brand'] = $brand;
    	
    	return $return_data;
    }
    
    protected function uploadExcel($param)
    {
    	//EXECL
    	$data_num = 0;
    	$data = array();
    	$filetmp =  $param['tmp_name'];
    	$rename = "./temp.xls";
    	//文件读取
    	require_once './Extend/PHPExcel_1.7.9/Classes/PHPExcel/IOFactory.php';
    	if ( !file_exists($rename) )
    	{
    		move_uploaded_file($filetmp,$rename);
    	}	    	
    	$objPHPExcel = PHPExcel_IOFactory::load($rename);
    	
    	/**读取excel文件中的第一个工作表*/
    	$currentSheet = $objPHPExcel->getSheet(0);
    	/**取得一共有多少行*/
    	$allRow = $currentSheet->getHighestRow();
	   	
    	/**从第二行开始输出，因为excel表中第一行为列名*/
    	for($currentRow = $param['start_row'];$currentRow <= $allRow;$currentRow++){
    		   $has_data = false;
    		   foreach ($param['col_info'] as $key => $val){
    		   	    $temp_data = $currentSheet->getCellByColumnAndRow($val,$currentRow)->getValue();
    		   	        		   	    
    		   	    if($temp_data instanceof PHPExcel_RichText)     //富文本转换字符串
    		   	    	$temp_data = $temp_data->__toString();    		       		   	        		   	     		   	  
    		   	    
    		   	    if($temp_data != ""){
    		   	    	$data[$data_num][$key] = $temp_data;
    		   	    	$has_data = true;
    		   	    }
    		   		
    		   }
    		   
    		   if ($param['add_info'] != null){
	    		   foreach ($param['add_info'] as $key => $val){
	    		   	$data[$data_num][$key]    = $val;
	    		   }
    		   }
    		   if ($has_data == true){
    		   		$data_num ++ ;
    		   }
    	}
    	//文件删除
    	unlink($rename);    	 
    	return $data;    	
    }
    
}
?>