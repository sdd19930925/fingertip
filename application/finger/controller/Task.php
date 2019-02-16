<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\finger\controller;

use controller\BasicAdmin;
use think\Db;

/**
 * 任务管理
 * Class Goods
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Task extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'FingerTask';

    /**
     * 任务管理
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '任务管理';

        $get = $this->request->get();
        if ($get['ty'] == 1) {//终端
            $this->_form_assign(2);//全部
        } else {
            $this->_form_assign(1);//商户
        }
        $db = Db::name($this->table)
            ->alias("t")
            ->field("t.*,p.cate_title as p_name,s.store_name as s_name,c.cate_title as c_name ")
            ->join("finger_platform p", "p.id=t.platform")
            ->join("finger_store s", "s.id=t.store_id")
            ->join("finger_task_cate c", "c.id=t.task_cate");
        if (isset($get['ty']) && $get['ty'] == 2) {
            $db->where(["t.uid" => session("user.id")]);
        }
        if (isset($get['title']) && $get['title'] !== '') {
            $db->whereLike('title', "%{$get['title']}%");
        }
        if (isset($get['store_id']) && $get['store_id'] !== '') {//匹配店铺
            $db->where('store_id', $get['store_id']);
        }
        if (isset($get['task_cate']) && $get['task_cate'] !== '') {//匹配分类
            $db->where('task_cate', $get['task_cate']);
        }
        if (isset($get['platform']) && $get['platform'] !== '') {//匹配平台
            $db->where('platform', $get['platform']);
        }
        if (isset($get['status']) && $get['status'] !== '') {//匹配状态
            $db->where('t.status', $get['status']);
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            // var_dump($get['create_at']);die;
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);

        }
        return parent::_list($db->order('create_at desc,id desc'));
    }

    public function _form_assign($ty)
    {
        $uid = session("user.id");
        $w = "1=1";
        if ($ty == 1) {
            $w .= " and uid='" . $uid . "'";
        }
        $cate = db("finger_task_cate")->select();
        $store = db("finger_store")->where($w)->select();
        $platform = db("finger_platform")->select();
        $this->assign(["cate" => $cate, "store" => $store, "platform" => $platform, "uid" => $uid]);
    }

    /**
     * 商品库存信息更新
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function examine()
    {
        if (!$this->request->post()) {
            $t_id = $this->request->get('id');
            $task = Db::name('FingerTask')->field("id,examine")->where(['id' => $t_id])->find();
            return $this->fetch('', ['vo' => $task]);
        }
        // 入库保存
        $goods_id = $this->request->post('id');
        list($post, $data) = [$this->request->post(), []];

        if (Db::name('FingerTask')->update($post) !== false) {
            $this->success('状态处理成功！', '');
        }
        $this->error('系统故障！');
    }

}
