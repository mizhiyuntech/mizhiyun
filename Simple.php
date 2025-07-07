<?php

namespace app\admin\controller\express;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 快递公司表（简化版）
 *
 * @icon fa fa-truck
 */
class Simple extends Backend
{

    /**
     * Simple模型对象
     * @var \app\admin\model\express\Simple
     */
    protected $model = null;

    /**
     * 快递类型
     * @var array
     */
    protected $expressTypeList = [];

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\express\Simple;
        
        // 获取快递类型列表
        $this->expressTypeList = $this->model->getExpressTypeList();
        
        // 向视图传递枚举值
        $this->view->assign("expressTypeList", $this->expressTypeList);
        $this->view->assign("paymentTypeList", $this->model->getPaymentTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id', 'express_type', 'company_name', 'payment_type', 'status', 'weigh', 'createtime', 'updatetime']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    
                    // 检查重复
                    if ($this->checkDuplicate($params)) {
                        $this->error(__('Express company already exists'));
                    }
                    
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    
                    // 检查重复（排除自己）
                    if ($this->checkDuplicate($params, $ids)) {
                        $this->error(__('Express company already exists'));
                    }
                    
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 获取快递公司列表（API接口）
     */
    public function getExpressList()
    {
        $expressType = $this->request->get('express_type', '');
        $paymentType = $this->request->get('payment_type', '');
        
        $where = ['status' => 'normal'];
        
        if ($expressType) {
            $where['express_type'] = $expressType;
        }
        
        if ($paymentType) {
            $where['payment_type'] = $paymentType;
        }
        
        $list = $this->model
            ->where($where)
            ->order('weigh desc, id desc')
            ->select();
            
        $this->success('获取成功', null, $list);
    }

    /**
     * 获取快递类型统计
     */
    public function getExpressTypeStats()
    {
        $stats = $this->model
            ->field('express_type, COUNT(*) as count')
            ->where('status', 'normal')
            ->group('express_type')
            ->select();
            
        $this->success('获取成功', null, $stats);
    }

    /**
     * 批量设置状态
     */
    public function setStatus()
    {
        $ids = $this->request->post('ids');
        $status = $this->request->post('status');
        
        if (!$ids || !in_array($status, ['normal', 'hidden'])) {
            $this->error(__('Parameter error'));
        }
        
        $ids = explode(',', $ids);
        
        try {
            $this->model->where('id', 'in', $ids)->update(['status' => $status]);
            $this->success();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 检查快递公司是否重复
     * @param array $params
     * @param int $excludeId 排除的ID
     * @return bool
     */
    protected function checkDuplicate($params, $excludeId = 0)
    {
        $where = [
            'express_type' => $params['express_type'],
            'company_name' => $params['company_name'],
            'payment_type' => $params['payment_type']
        ];
        
        if ($excludeId) {
            $where['id'] = ['neq', $excludeId];
        }
        
        $count = $this->model->where($where)->count();
        return $count > 0;
    }

    /**
     * 获取快递公司选择器数据
     */
    public function selectpage()
    {
        return parent::selectpage();
    }
}
