
/*店铺所属品牌*/
ALTER TABLE  `tp_wecha_shop` ADD  `BelongBrand` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `logo` ;

/*商品详情图片*/
ALTER TABLE  `tp_item` ADD `imagesDetail` BLOB NULL AFTER `Uninum` ;

-- --------------------------------------------------------

--
-- Table structure for table `tp_message_check`
--

CREATE TABLE IF NOT EXISTS `tp_message_check` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
