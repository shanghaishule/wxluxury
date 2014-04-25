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