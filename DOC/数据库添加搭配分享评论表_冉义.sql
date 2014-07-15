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