-- FastAdmin 快递公司数据表（简化版）
-- 表名：fa_express_simple
-- 只包含核心字段：快递类型、快递公司、现结或月结

DROP TABLE IF EXISTS `fa_express_simple`;

CREATE TABLE `fa_express_simple` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `express_type` varchar(50) NOT NULL DEFAULT '' COMMENT '快递类型',
  `company_name` varchar(100) NOT NULL DEFAULT '' COMMENT '快递公司',
  `payment_type` enum('现结','月结') NOT NULL DEFAULT '现结' COMMENT '结算方式',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `weigh` int(10) NOT NULL DEFAULT 0 COMMENT '权重',
  `createtime` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `weigh` (`weigh`,`id`),
  KEY `status` (`status`),
  KEY `express_type` (`express_type`),
  KEY `payment_type` (`payment_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='快递公司表（简化版）';

-- 插入示例数据
INSERT INTO `fa_express_simple` (`id`, `express_type`, `company_name`, `payment_type`, `status`, `weigh`, `createtime`, `updatetime`) VALUES
(1, '标准快递', '顺丰速运', '月结', 'normal', 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, '标准快递', '中通快递', '现结', 'normal', 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, '标准快递', '韵达快递', '月结', 'normal', 80, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, '标准快递', '圆通快递', '现结', 'normal', 70, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, '标准快递', '申通快递', '月结', 'normal', 60, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(6, '同城快递', '闪送', '现结', 'normal', 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '同城快递', '达达快送', '现结', 'normal', 40, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '国际快递', 'DHL', '月结', 'normal', 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(9, '国际快递', 'FedEx', '月结', 'normal', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(10, '国际快递', 'UPS', '月结', 'normal', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());