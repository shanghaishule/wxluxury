/*本脚本可以反复执行，重复执行*/

/*商品编号*/
ALTER TABLE  `tp_item` ADD  `Uninum` VARCHAR( 50 ) NOT NULL COMMENT  '商品编号' AFTER `tokenTall` ;

/* 经纬度*/
ALTER TABLE `tp_wecha_shop` ADD `longitude` VARCHAR( 80) NOT NULL AFTER `BelongBrand` ,
 ADD `latitude` VARCHAR( 80) NOT NULL AFTER `longitude` ;
