ALTER TABLE `tp_item` ADD `favi` INT( 10) NOT NULL DEFAULT'0' COMMENT'收藏数目';
ALTER TABLE `tp_item_taobao`ADD`item_model` INT( 4)NOT NULL AFTER `Huohao` ;