/*
Navicat MySQL Data Transfer

Source Server         : aaa
Source Server Version : 50532
Source Host           : localhost:3306
Source Database       : wxluxury

Target Server Type    : MYSQL
Target Server Version : 50532
File Encoding         : 65001

Date: 2014-05-30 17:19:12
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `tp_match_comments`
-- ----------------------------
DROP TABLE IF EXISTS `tp_match_comments`;
CREATE TABLE `tp_match_comments` (
  `match_id` int(20) NOT NULL,
  `uid` int(20) NOT NULL,
  `comments` varchar(50) NOT NULL,
  `addtime` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tp_match_comments
-- ----------------------------
INSERT INTO `tp_match_comments` VALUES ('1', '1', '好漂亮啊', '1023545645');
INSERT INTO `tp_match_comments` VALUES ('1', '18', '真的挺不错的', '1013136216');

--wecha_shop添加店铺级别
ALTER TABLE `tp_wecha_shop` ADD  `level` int(5) NOT NULL DEFAULT 0;

--添加我的品牌积分表--
DROP TABLE IF EXISTS `tp_brandpoints`;
CREATE TABLE `tp_brandpoints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `brandid` int(11) DEFAULT '0',
  `points` int(11) DEFAULT '0',
  `num` int(11) DEFAULT '1',
  `used_points` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tp_brandpoints
-- ----------------------------
INSERT INTO `tp_brandpoints` VALUES ('1', '31', '45', '500', '1', '20');
INSERT INTO `tp_brandpoints` VALUES ('2', '31', '42', '200', '1', '10');



