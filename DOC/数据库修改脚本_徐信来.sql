/*本脚本可以反复执行，重复执行*/
DROP TABLE IF EXISTS `tp_upload_shop`;
CREATE TABLE IF NOT EXISTS `tp_upload_shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provice` varchar(128) NOT NULL,
  `city` varchar(128) NOT NULL, 
  `brand_name` varchar(128) NOT NULL,    
  `shop_name` varchar(128) NOT NULL,  
  `lbs_addr` varchar(256) NOT NULL,
  `show_addr` varchar(256) NOT NULL,
  `phone` varchar(128) ,  
   PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tp_set_promotion`;
CREATE TABLE IF NOT EXISTS `tp_set_promotion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tokenTall` varchar(20) NOT NULL DEFAULT '' COMMENT '商家token',
  `brand_id` int(11) NOT NULL,
  `theme` text,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,  
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0-未开始 1-已经开始 2-已经结束',
  `img` varchar(180) NOT NULL,
  `discount_rate` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

alter table tp_item add promotion_id varchar(100);
