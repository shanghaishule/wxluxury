<?php
function isAndroid(){
	if(strstr($_SERVER['HTTP_USER_AGENT'],'Android')) {
		return 1;
	}
	return 0;
}
function attach($attach, $type) {
	if (false === strpos($attach, 'http://')) {
		//本地附件
		return __ROOT__ . '/weTall/data/upload/' . $type . '/' . $attach;
		//远程附件
		//todo...
	} else {
		//URL链接
		return $attach;
	}
	
}
//导出为excel
/**
 * 导出数据为excel表格
 *@param $data    一个二维数组,结构如同从数据库查出来的数组
 *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
 *@param $filename 下载的文件名
 *@examlpe
 */
function exportexcel($data=array(),$title=array(),$filename='report'){
	header("Content-type:application/octet-stream");
	header("Accept-Ranges:bytes");
	header("Content-type:application/vnd.ms-excel");
	header("Content-Disposition:attachment;filename=".$filename.".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	//导出xls 开始
	if (!empty($title)){
		foreach ($title as $k => $v) {
			$title[$k]=iconv("UTF-8", "GB2312",$v);
		}
		$title= implode("\t", $title);
		echo "$title\n";
	}
		if (!empty($data)){
		foreach($data as $key=>$val){
		foreach ($val as $ck => $cv) {
		$data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
		}
		$data[$key]=implode("\t", $data[$key]);

		}
		echo implode("\n",$data);
		}
?>