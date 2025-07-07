CREATE TABLE `fa_contract` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `contract_type` enum('purchase','commercial') NOT NULL DEFAULT 'purchase' COMMENT '合同类型(purchase=采购合同,commercial=商业合同)',
  `contract_name` varchar(100) NOT NULL DEFAULT '' COMMENT '合同名称',
  `effective_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '生效日期',
  `remarks` text COMMENT '备注',
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled' COMMENT '状态(enabled=启用,disabled=禁用)',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='合同管理表';
