<?php

namespace app\admin\model\express;

use think\Model;
use think\model\concern\SoftDelete;

class Simple extends Model
{
    // 表名
    protected $name = 'express_simple';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'payment_type_text',
        'status_text'
    ];
    
    // 字段验证规则
    protected $rule = [
        'express_type' => 'require|max:50',
        'company_name' => 'require|max:8|regex:/^(?!\d+$).*/',
        'payment_type' => 'require|in:现结,月结',
        'status' => 'require|in:normal,hidden'
    ];

    // 字段验证提示
    protected $message = [
        'express_type.require' => '快递类型不能为空',
        'express_type.max' => '快递类型长度不能超过50个字符',
        'company_name.require' => '快递公司不能为空',
        'company_name.max' => '快递公司名称长度不能超过8位',
        'company_name.regex' => '快递公司名称不能为纯数字',
        'payment_type.require' => '结算方式不能为空',
        'payment_type.in' => '结算方式只能是现结或月结',
        'status.require' => '状态不能为空',
        'status.in' => '状态只能是normal或hidden'
    ];

    protected static function init()
    {
        self::afterInsert(function ($row) {
            if (!$row['weigh']) {
                $pk = $row->getPk();
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            }
        });
    }

    /**
     * 获取快递类型列表
     */
    public function getExpressTypeList()
    {
        return [
            '标准快递' => __('标准快递'),
            '同城快递' => __('同城快递'),
            '国际快递' => __('国际快递'),
            '冷链快递' => __('冷链快递'),
            '特快专递' => __('特快专递')
        ];
    }

    /**
     * 获取结算方式列表
     */
    public function getPaymentTypeList()
    {
        return [
            '现结' => __('现结'),
            '月结' => __('月结')
        ];
    }

    /**
     * 获取状态列表
     */
    public function getStatusList()
    {
        return [
            'normal' => __('Normal'),
            'hidden' => __('Hidden')
        ];
    }

    /**
     * 获取结算方式文本属性
     */
    public function getPaymentTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['payment_type'] ?? '');
        $list = $this->getPaymentTypeList();
        return $list[$value] ?? '';
    }

    /**
     * 获取状态文本属性
     */
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    /**
     * 根据快递类型获取快递公司列表
     */
    public static function getCompanyByType($expressType = '')
    {
        $where = ['status' => 'normal'];
        if ($expressType) {
            $where['express_type'] = $expressType;
        }
        
        return self::where($where)
            ->order('weigh desc, id desc')
            ->column('company_name', 'id');
    }

    /**
     * 根据结算方式获取快递公司列表
     */
    public static function getCompanyByPayment($paymentType = '')
    {
        $where = ['status' => 'normal'];
        if ($paymentType) {
            $where['payment_type'] = $paymentType;
        }
        
        return self::where($where)
            ->order('weigh desc, id desc')
            ->select();
    }

    /**
     * 获取所有正常状态的快递公司（用于下拉选择）
     */
    public static function getSelectOptions()
    {
        return self::where('status', 'normal')
            ->order('weigh desc, id desc')
            ->column('company_name', 'id');
    }

    /**
     * 检查快递公司是否存在
     */
    public static function checkCompanyExists($companyName, $excludeId = 0)
    {
        $where = ['company_name' => $companyName];
        if ($excludeId > 0) {
            $where[] = ['id', '<>', $excludeId];
        }
        
        return self::where($where)->count() > 0;
    }

    /**
     * 获取快递统计信息
     */
    public static function getStatistics()
    {
        $total = self::count();
        $normal = self::where('status', 'normal')->count();
        $hidden = self::where('status', 'hidden')->count();
        
        $paymentStats = self::field('payment_type, COUNT(*) as count')
            ->group('payment_type')
            ->select()
            ->toArray();
        
        $typeStats = self::field('express_type, COUNT(*) as count')
            ->group('express_type')
            ->select()
            ->toArray();
        
        return [
            'total' => $total,
            'normal' => $normal,
            'hidden' => $hidden,
            'payment_stats' => $paymentStats,
            'type_stats' => $typeStats
        ];
    }

    /**
     * 批量更新状态
     */
    public static function batchUpdateStatus($ids, $status)
    {
        if (!is_array($ids) || empty($ids)) {
            return false;
        }
        
        return self::where('id', 'in', $ids)->update(['status' => $status]);
    }

    /**
     * 批量更新权重
     */
    public static function batchUpdateWeigh($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        
        $result = true;
        foreach ($data as $id => $weigh) {
            if (!self::where('id', $id)->update(['weigh' => $weigh])) {
                $result = false;
            }
        }
        
        return $result;
    }

    /**
     * 搜索快递公司
     */
    public static function search($keyword, $expressType = '', $paymentType = '')
    {
        $where = [];
        
        if ($keyword) {
            $where[] = ['company_name', 'like', '%' . $keyword . '%'];
        }
        
        if ($expressType) {
            $where['express_type'] = $expressType;
        }
        
        if ($paymentType) {
            $where['payment_type'] = $paymentType;
        }
        
        return self::where($where)
            ->order('weigh desc, id desc')
            ->paginate(10);
    }

    /**
     * 获取推荐的快递公司（权重高的）
     */
    public static function getRecommended($limit = 5)
    {
        return self::where('status', 'normal')
            ->order('weigh desc, id desc')
            ->limit($limit)
            ->select();
    }
}
