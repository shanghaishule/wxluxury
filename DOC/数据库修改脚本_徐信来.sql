/*本脚本可以反复执行，重复执行*/
DROP TABLE IF EXISTS `tp_upload_shop`;
CREATE TABLE IF NOT EXISTS `tp_upload_shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(128) NOT NULL,    
  `shop_name` varchar(128) NOT NULL,  
  `lbs_addr` varchar(256) NOT NULL,
  `show_addr` varchar(256) NOT NULL,
  `phone` varchar(128) ,  
   PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;