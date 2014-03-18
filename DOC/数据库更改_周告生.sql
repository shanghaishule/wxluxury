
/*店铺所属品牌*/
ALTER TABLE  `tp_wecha_shop` ADD  `BelongBrand` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `logo` ;

/*商品详情图片*/
ALTER TABLE  `tp_item` ADD `imagesDetail` BLOB NULL AFTER `Uninum` ;
