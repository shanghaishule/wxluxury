ALTER TABLE `tp_item` ADD `favi` INT( 10) NOT NULL DEFAULT'0' COMMENT'收藏数目';
ALTER TABLE `tp_item_taobao`ADD`item_model` INT( 4)NOT NULL AFTER `Huohao` ;
ALTER TABLE  `tp_item` ADD  `detail_stock` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER  `images` ;

ALTER TABLE  `tp_item` CHANGE  `intro`  `intro` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ;
ALTER TABLE  `tp_order_detail` CHANGE  `size`  `size` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';

/*店铺的冻结状态*/
ALTER TABLE  `tp_wecha_shop` ADD  `frozen` INT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '0-冻结 1-正常 2-审核中' AFTER  `latitude` ;

ALTER TABLE  `tp_wecha_shop` ADD  `owner` VARCHAR( 100 ) NOT NULL AFTER  `shop_city` ,
ADD  `IDno` VARCHAR( 18 ) NOT NULL AFTER  `owner` ,
ADD  `email` VARCHAR( 180 ) NOT NULL AFTER  `IDno` ,
ADD  `licence_img` VARCHAR( 200 ) NOT NULL AFTER  `email` ;

ALTER TABLE  `tp_shop_favi` CHANGE  `tokenTall`  `item_id` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT  '商品的id';

ALTER TABLE  `tp_user` ADD  `brand_jifen` TEXT NULL AFTER  `email` ;
ALTER TABLE  `tp_brandlist` ADD  `jifen` INT( 5 ) NOT NULL DEFAULT  '0' AFTER  `imgurl` ;
ALTER TABLE  `tp_wecha_shop` ADD  `qq` VARCHAR( 13 ) NULL AFTER  `licence_img` ;

/*04-26*/
ALTER TABLE  `tp_item_taobao` ADD  `detail_stock` TEXT NOT NULL ,
ADD  `old_price` FLOAT( 7 ) NOT NULL DEFAULT  '0';

/*05-02*/
DROP TABLE IF EXISTS `tp_application`;
CREATE TABLE IF NOT EXISTS `tp_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(10) NOT NULL COMMENT '用户id',
  `uname` varchar(30) DEFAULT NULL COMMENT '用户名',
  `QQ` varchar(30) DEFAULT NULL COMMENT '用户邮箱',
  `applicant` varchar(30) DEFAULT NULL COMMENT '申请人',
  `addr` varchar(255) DEFAULT NULL COMMENT '地址',
  `phone` varchar(50) DEFAULT NULL COMMENT '电话',
  `brand` varchar(255) DEFAULT NULL COMMENT '品牌',
  `provice` varchar(255) DEFAULT NULL COMMENT '省份',
  `trueshop` smallint(1) DEFAULT '1' COMMENT '是否有实体店',
  `city` varchar(500) DEFAULT NULL COMMENT '其他说明',
  `createtime` int(20) DEFAULT NULL COMMENT '申请时间',
  `uptatetime` int(20) DEFAULT NULL,
  `tokenTall` varchar(30) NOT NULL,
  `re` varchar(500) DEFAULT NULL,
  `wecha_id` varchar(200) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

ALTER TABLE  `tp_upload_shop` ADD  `lat` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER  `phone` ,
ADD  `longtitude` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER  `lat` ;

ALTER TABLE  `tp_upload_shop` ADD  `status` INT( 3 ) NOT NULL DEFAULT  '0' COMMENT  '被领取数目' AFTER  `longtitude` ;
ALTER TABLE  `tp_upload_shop` ADD  `tokenTall` VARCHAR( 30 ) NULL DEFAULT NULL AFTER  `status` ;
ALTER TABLE  `tp_wecha_shop` ADD  `qq` INT( 15 ) NULL AFTER  `licence_img` ;

/*5-7*/

DROP TABLE IF EXISTS `tp_atixian`;
CREATE TABLE IF NOT EXISTS `tp_atixian` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `tokenTall` varchar(19) NOT NULL,
  `hadti` double NOT NULL DEFAULT '0',
  `yaoti` double NOT NULL,
  `status` int(1) NOT NULL COMMENT '0 - 审核中 1-已经提取 ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE  `tp_atixian` CHANGE  `status`  `status` INT( 1 ) NOT NULL DEFAULT  '1' COMMENT  '0 - 审核中 1-已经提取 ';

/*5-10*/
ALTER TABLE  `tp_atixian` CHANGE  `status`  `status` INT( 1 ) NOT NULL DEFAULT  '1' COMMENT  '0 - 审核中 1-已经提取  2-已经导出';
ALTER TABLE  `tp_account_setting` CHANGE  `bankname`  `bankname` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '支付宝' COMMENT  '银行名称';

/*6-10*/
CREATE TABLE IF NOT EXISTS `tp_aused_taobao` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `brand_id` int(3) NOT NULL,
  `url` varchar(250) NOT NULL COMMENT '天猫淘宝地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='使用过的淘宝天猫导入地址与品牌' AUTO_INCREMENT=1 ;

