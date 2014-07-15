-- 处理admin登录不了的问题
ALTER TABLE `tp_user` ADD  `username` varchar(50) NOT NULL;
ALTER TABLE `tp_user` ADD  `password` char(32) NOT NULL;
ALTER TABLE `tp_user` ADD  `role` smallint(6) unsigned NOT NULL COMMENT '组ID';
ALTER TABLE `tp_user` ADD  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1:启用 0:禁止';
ALTER TABLE `tp_user` ADD  `remark` varchar(255) DEFAULT NULL COMMENT '备注说明';
ALTER TABLE `tp_user` ADD  `last_location` varchar(100) DEFAULT NULL COMMENT '最后登录位置';
ALTER TABLE `tp_user` ADD  `email` varchar(90) NOT NULL DEFAULT '';
  
update `tp_user` set `username` = 'admin', `password` = 'b80c4de605487af2bf83a7cbd1d68025', `role` = '5', `status` = '1' where id = 1;

-- 增加商品入库模块
insert into tp_menu(NAME,pid,module_name,action_name,often,ordid,display)
values('商品入库',51,'item_ruku','index',0,7,1);
