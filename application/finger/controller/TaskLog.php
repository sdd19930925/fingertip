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
 * 任务记录管理
 * Class Goods
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class TaskLog extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'FingerTaskLog';

    /**
     * 任务记录管理
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '任务记录管理';
        $get = $this->request->get();
        if ($get['ty'] == 1) {
            $this->_form_assign(2);//全部
        } else {
            $this->_form_assign(1);//商户
        }
        $db = Db::name($this->table)
            ->alias("ftl")
            ->field("ftl.*,ft.store_id,fs.store_name,fs.store_num,ft.task_cate,ftl.order_id,ft.price,fu.nickname,su.username")
            ->join("finger_task ft", "ft.id=ftl.t_id")//任务id
            ->join("finger_store fs", "fs.id=ft.store_id")
            ->join("finger_user fu", "fu.id=ftl.mid")
            ->join("finger_task_cate ftc", "ftc.id=ft.task_cate")
            ->join("system_user su", "su.id=ftl.uid");
        if (isset($get['ty']) && $get['ty'] == 2) {//客户端
            $db->where(["ftl.uid" => session("user.id")]);
        }
        if (isset($get['store_name']) && $get['store_name'] !== '') {//匹配店铺名
            $db->whereLike('fs.store_name', "%{$get['store_name']}%");
        }
        if (isset($get['store_num']) && $get['store_num'] !== '') {//匹配旺旺号
            $db->whereLike('fs.store_num', "%{$get['store_num']}%");
        }
        if (isset($get['tl_id']) && $get['tl_id'] !== '') {//匹配订单id
            $db->whereLike('ftl.id', "%{$get['tl_id']}%");
        }
        if (isset($get['t_id']) && $get['t_id'] !== '') {//匹配任务id
            $db->where('ftl.t_id', $get['t_id']);
        }
        if (isset($get['order_id']) && $get['order_id'] != "") {//匹配京东/淘宝编号

            $db->whereLike('ftl.order_id', "%{$get['order_id']}%");
        }
        if (isset($get['mid']) && $get['mid'] != "") {//匹配用户id

            $db->where('ftl.mid', $get['mid']);
        }
        if (isset($get['task_cate']) && $get['task_cate'] != "") {//匹配订单类型

            $db->where('ftc.task_cate', $get['task_cate']);
        }
        if (isset($get['status']) && $get['status'] != "") {//匹配订单状态

            $db->where('ftl.status', $get['status']);
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('ftl.create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);

        }
        return parent::_list($db->order('ftl.create_at desc,ftl.id desc'));
    }

    public function _form_assign($ty)
    {
        $w = "1=1";
        if ($ty == 1) {
            $w .= " and uid='" . session("user.id") . "'";
        }
        $store = Db::name("finger_store")->where($w)->select();//店铺
        $task = Db::name("finger_task")->where($w)->select();//任务
        $task_cate = Db::name("finger_task_cate")->field("id,cate_title")->select();//任务
        $mem = Db::name("finger_user")->select();
        $platform = Db::name("finger_platform")->select();//平台
        $user = Db::name("system_user")->select();
        $this->assign(["store" => $store, "platform" => $platform, "task" => $task, "mem" => $mem, "user" => $user, "task_cate" => $task_cate]);
    }


}
