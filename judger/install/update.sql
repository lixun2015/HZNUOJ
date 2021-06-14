-- ----------------------------
-- 2020/3/30 DATABASE update by lixun2015
-- ----------------------------
set names utf8;
use jol;
ALTER TABLE `contest` ADD COLUMN `user_id` VARCHAR(48) NOT NULL DEFAULT 'admin' AFTER `password`;
ALTER TABLE `contest` ADD COLUMN `isTop`  tinyint(1) NOT NULL DEFAULT 0 AFTER `practice`;
ALTER TABLE `solution` MODIFY COLUMN `pass_rate` DECIMAL(3,2) UNSIGNED NOT NULL DEFAULT '0.00';
ALTER TABLE `printer_code` MODIFY COLUMN `user_id` CHAR(48) NOT NULL;
ALTER TABLE `privilege` MODIFY COLUMN `user_id` CHAR(48) NOT NULL;
ALTER TABLE `solution` MODIFY COLUMN `user_id` CHAR(48) NOT NULL;
ALTER TABLE `problemset` MODIFY COLUMN `index` int(11) NOT NULL AUTO_INCREMENT FIRST ;
ALTER TABLE `problemset` ADD COLUMN `access_level` tinyint NOT NULL DEFAULT 0;
ALTER TABLE `hit_log` MODIFY COLUMN `ip` varchar(46) DEFAULT NULL;
ALTER TABLE `loginlog` MODIFY COLUMN `ip` varchar(46) DEFAULT NULL;
ALTER TABLE `online` MODIFY COLUMN `ip` varchar(46) CHARACTER SET utf8 NOT NULL DEFAULT '';
ALTER TABLE `reply` MODIFY COLUMN `ip` varchar(46) DEFAULT NULL;
ALTER TABLE `solution` MODIFY COLUMN `ip` char(46) NOT NULL;
ALTER TABLE `team` MODIFY COLUMN `ip` varchar(46) DEFAULT NULL;
ALTER TABLE `users` MODIFY COLUMN `ip` varchar(46) NOT NULL DEFAULT '';
ALTER TABLE `users` ADD COLUMN `access_level` tinyint NOT NULL DEFAULT 0;
-- Dump completed on 2019-03-13 17:03:43
-- ----------------------------
-- Table structure for `class_list`
-- ----------------------------
CREATE TABLE `class_list` (
  `class_name` varchar(100) NOT NULL,
  `enrollment_year` smallint(4) NOT NULL,
  PRIMARY KEY (`class_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Dumping data for table class_list
-- ----------------------------
INSERT INTO `class_list` VALUES ('其它', '0');

-- ----------------------------
-- Table structure for `reg_code`
-- ----------------------------
CREATE TABLE `reg_code` (
  `class_name` varchar(100) NOT NULL,
  `reg_code` varchar(100) NOT NULL,
  `remain_num` smallint(4) NOT NULL,
  PRIMARY KEY (`class_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Dumping data for table reg_code
-- ----------------------------
INSERT INTO `reg_code` VALUES ('其它', '', '0');

-- ----------------------------
-- Table structure for `course`
-- ----------------------------
CREATE TABLE `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '10000',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `isProblem` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- Records of course
-- ----------------------------
INSERT INTO `course` VALUES ('1', '入门篇', '0', '0', '0');
INSERT INTO `course` VALUES ('2', '九阴真经', '1', '0', '0');
INSERT INTO `course` VALUES ('3', '九阳神功', '2', '0', '0');
INSERT INTO `course` VALUES ('4', '葵花宝典', '3', '0', '0');
INSERT INTO `course` VALUES ('5', '辟邪剑谱', '4', '0', '0');
INSERT INTO `course` VALUES ('6', '平台操作题', '0', '1', '0');
INSERT INTO `course` VALUES ('7', '输出题入门', '1', '1', '0');
INSERT INTO `course` VALUES ('8', '计算题入门', '2', '1', '0');
INSERT INTO `course` VALUES ('9', '分支结构入门', '3', '1', '0');
INSERT INTO `course` VALUES ('10', '循环结构入门', '4', '1', '0');
INSERT INTO `course` VALUES ('11', '1000', '0', '6', '1');

-- 2021年升级
ALTER TABLE `contest_problem` ADD COLUMN `c_accepted` int(11) NOT NULL DEFAULT '0' AFTER `num`;
ALTER TABLE `contest_problem` ADD COLUMN `c_submit` int(11) NOT NULL DEFAULT '0' AFTER `c_accepted`;
UPDATE `contest_problem` SET `c_submit`=(SELECT count(*) FROM `solution` WHERE `problem_id`=`contest_problem`.`problem_id` AND contest_id=`contest_problem`.`contest_id`);
UPDATE `contest_problem` SET `c_accepted`=(SELECT count(*) FROM `solution` WHERE `problem_id`=`contest_problem`.`problem_id` AND `result`=4 AND contest_id=`contest_problem`.`contest_id`);
ALTER TABLE `problem` CHANGE `time_limit` `time_limit` DECIMAL(10,3) NOT NULL DEFAULT '0';
ALTER TABLE `users` ADD COLUMN `points` decimal(10,2) DEFAULT '0.00';
ALTER TABLE `solution` ADD `lastresult` SMALLINT(6) NOT NULL DEFAULT '0' AFTER `judger`;
UPDATE `solution` SET `lastresult`=`result`;
CREATE TABLE IF NOT EXISTS `points_log` (
  `index` int(11) NOT NULL AUTO_INCREMENT,
  `item` varchar(100) NOT NULL,
  `operator` varchar(48) NOT NULL DEFAULT '',
  `user_id` varchar(48) NOT NULL DEFAULT '',
  `solution_id` int(11) NOT NULL DEFAULT '0',
  `pay_type` tinyint(4) NOT NULL,
  `pay_points` DECIMAL(10,2) NOT NULL,
  `pay_time` DATETIME NOT NULL,
  PRIMARY KEY (`index`),
  KEY `solution_id` (`solution_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `class_list` ADD `give_points` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `enrollment_year`;
ALTER TABLE `users` ADD `activateCode` VARCHAR(48) NOT NULL DEFAULT '' AFTER `points`;
ALTER TABLE `users` ADD `activateTimelimit` datetime DEFAULT NULL AFTER `activateCode`;
