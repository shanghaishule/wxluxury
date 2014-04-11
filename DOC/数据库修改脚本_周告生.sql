/*本脚本可以反复执行，重复执行*/

/*商品编号*/
ALTER TABLE  `tp_item` ADD  `Uninum` VARCHAR( 50 ) NOT NULL COMMENT  '商品编号' AFTER `tokenTall` ;

/* 经纬度*/
ALTER TABLE `tp_wecha_shop` ADD `longitude` VARCHAR( 80) NOT NULL AFTER `BelongBrand` ,
 ADD `latitude` VARCHAR( 80) NOT NULL AFTER `longitude` ;

 
 ALTER TABLE  `tp_wecha_shop` ADD  `shop_city` VARCHAR( 10 ) NOT NULL DEFAULT  '上海' AFTER  `latitude` ;
 
 /*一元购*/
 ALTER TABLE  `tp_item`ADD  `old_price` FLOAT NO TNULL DEFAULT'0' AFTER `Huohao` ,
 ADD `Oneyuan` INT( 1) NOT NULL DEFAULT '0' COMMENT '0-不参加一元购 1-参加' AFTER `old_price` ;
 
 /*参加打折*/
 CREATE TABLE IF NOT EXISTS `tp_discount_shop` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `dimg` varchar(180) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `theme` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*设置促销活动*/
INSERT INTO `wxluxury`.`tp_menu` (
 `id` ,
 `name` ,
 `pid` ,
 `module_name` ,
 `action_name` ,
 `data` ,
 `remark` ,
 `often` ,
 `ordid` ,
 `display` 
)
VALUES (
 NULL ,'设置促销活动','148','set_discount','index','','','99','1','1'
);
CREATE TABLE IF NOT EXISTS `tp_set_discount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_id` int(11) NOT NULL,
  `theme` text,
  `date` int(10) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0-未开始 1-已经开始 2-已经结束',
  `img` varchar(180) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
/*浏览量*/
ALTER TABLE `tp_brandlist` CHANGE `tokenTall` `volume` INT( 11) NOT NULL DEFAULT'0';
/*图片*/
ALTER TABLE `tp_brandlist` ADD `imgurl` VARCHAR( 190) NULL ;
/*品牌区域*/
ALTER TABLE `tp_brandlist` ADD `domain` INT( 1) NOT NULL DEFAULT'1' COMMENT'0-国内 1-国外';