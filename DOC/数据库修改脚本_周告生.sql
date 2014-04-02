/*本脚本可以反复执行，重复执行*/

/*商品编号*/
ALTER TABLE  `tp_item` ADD  `Uninum` VARCHAR( 50 ) NOT NULL COMMENT  '商品编号' AFTER `tokenTall` ;

/* 经纬度*/
ALTER TABLE `tp_wecha_shop` ADD `longitude` VARCHAR( 80) NOT NULL AFTER `BelongBrand` ,
 ADD `latitude` VARCHAR( 80) NOT NULL AFTER `longitude` ;

 
 ALTER TABLE  `tp_wecha_shop` ADD  `shop_city` VARCHAR( 10 ) NOT NULL DEFAULT  '上海' AFTER  `latitude` ;
 
 ALTER TABLE  `tp_item`ADD  `old_price` FLOAT NO TNULL DEFAULT'0' AFTER `Huohao` ,
 ADD `Oneyuan` INT( 1) NOT NULL DEFAULT '0' COMMENT '0-不参加一元购 1-参加' AFTER `old_price` ;