
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
  `text` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*货号*/
ALTER TABLE  `tp_item` ADD  `Huohao` VARCHAR( 255 ) NOT NULL AFTER  `imagesDetail` ;

CREATE TABLE IF NOT EXISTS `tp_item_taobao` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` smallint(4) unsigned DEFAULT NULL,
  `orig_id` smallint(6) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `intro` varchar(255) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `rates` float(8,2) NOT NULL COMMENT '佣金比率xxx.xx%',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:商品,2:图片',
  `comments` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `cmt_taobao_time` int(10) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) NOT NULL,
  `ordid` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `info` text,
  `news` tinyint(4) DEFAULT '0',
  `tuijian` tinyint(4) DEFAULT '0',
  `goods_stock` int(11) DEFAULT '50' COMMENT '库存',
  `buy_num` int(11) DEFAULT '0' COMMENT '卖出数量',
  `brand` int(11) DEFAULT '1' COMMENT '品牌',
  `pingyou` decimal(10,2) DEFAULT '0.00',
  `kuaidi` decimal(10,2) DEFAULT '0.00',
  `ems` decimal(10,2) DEFAULT '0.00',
  `free` int(11) DEFAULT '1',
  `color` varchar(255) DEFAULT NULL COMMENT '颜色',
  `size` varchar(255) DEFAULT NULL COMMENT '尺寸',
  `tokenTall` varchar(20) NOT NULL DEFAULT '',
  `Uninum` varchar(50) NOT NULL COMMENT '商品编号',
  `imagesDetail` BLOB NULL,
  `Huohao` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cid` (`cate_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=144 ;

ALTER TABLE  `tp_item_taobao` ADD  `images` VARCHAR( 255 ) NULL DEFAULT  '|1001|1002|1003|1004|1005' AFTER  `Huohao`;
ALTER TABLE  `tp_item` ADD  `images` VARCHAR( 255 ) NULL DEFAULT  '|1001|1002|1003|1004|1005' AFTER  `Huohao`;

ALTER TABLE  `tp_wecha_shop` CHANGE  `BelongBrand`  `BelongBrand` INT( 11 ) NOT NULL DEFAULT  '-1';

ALTER TABLE `tp_item `ADD` item_model` INT( 4) NOT NULL DEFAULT'2014' COMMENT'哪一个款' AFTER`Huohao` ;

ALTER TABLE `tp_brandlist` ADD `imgurl` VARCHAR( 190) NULL DEFAULT NULL COMMENT'320*128';
