-- FastAdmin 快递公司数据表
-- 表名：fa_express_company

DROP TABLE IF EXISTS `fa_express_company`;

CREATE TABLE `fa_express_company` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `express_type` varchar(50) NOT NULL DEFAULT '' COMMENT '快递类型',
  `company_name` varchar(100) NOT NULL DEFAULT '' COMMENT '快递公司名称',
  `company_code` varchar(50) NOT NULL DEFAULT '' COMMENT '快递公司代码',
  `payment_type` enum('现结','月结') NOT NULL DEFAULT '现结' COMMENT '结算方式',
  `contact_person` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人',
  `contact_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
  `contact_address` varchar(255) NOT NULL DEFAULT '' COMMENT '联系地址',
  `service_area` text COMMENT '服务区域',
  `price_info` text COMMENT '价格信息',
  `remark` text COMMENT '备注',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `weigh` int(10) NOT NULL DEFAULT 0 COMMENT '权重',
  `createtime` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `weigh` (`weigh`,`id`),
  KEY `status` (`status`),
  KEY `express_type` (`express_type`),
  KEY `payment_type` (`payment_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='快递公司表';

-- 插入一些示例数据
INSERT INTO `fa_express_company` (`id`, `express_type`, `company_name`, `company_code`, `payment_type`, `contact_person`, `contact_phone`, `contact_address`, `service_area`, `price_info`, `remark`, `status`, `weigh`, `createtime`, `updatetime`) VALUES
(1, '标准快递', '顺丰速运', 'SF', '月结', '张经理', '400-111-1111', '深圳市福田区', '全国', '首重12元/kg，续重2元/kg', '时效快，服务好', 'normal', 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, '标准快递', '中通快递', 'ZTO', '现结', '李经理', '400-222-2222', '上海市青浦区', '全国', '首重8元/kg，续重1.5元/kg', '性价比高', 'normal', 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, '标准快递', '韵达快递', 'YD', '月结', '王经理', '400-333-3333', '上海市青浦区', '全国', '首重8元/kg，续重1.5元/kg', '网点覆盖广', 'normal', 80, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, '同城快递', '闪送', 'SS', '现结', '刘经理', '400-444-4444', '北京市朝阳区', '同城', '起步价12元，超出按距离计费', '同城即时配送', 'normal', 70, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, '国际快递', 'DHL', 'DHL', '月结', '陈经理', '400-555-5555', '上海市浦东新区', '国际', '按重量和目的地计费', '国际快递领先品牌', 'normal', 60, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());