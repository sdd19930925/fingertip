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
 * 申诉管理
 * Class Goods
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Recharge extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'FingerAppeal';

    /**
     * 申诉管理
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '充值管理';
        $get = $this->request->get();
        $this->_form_assign();
        $db = Db::name($this->table)
            ->alias("fa")
            ->field("fa.*,su.nickname")
            ->join("finger_user su", "su.id=fa.uid");
        if (isset($get['ty']) && $get['ty'] == 2) {//客户端
            $db->where(["fa.uid" => session("user.id")]);
        }
        if (isset($get['task_id']) && $get['task_id'] != "") {//订单id
            $db->where(["fa.task_id" => $get['task_id']]);
        }
        if (isset($get['title']) && $get['title'] !== '') {//标题
            $db->whereLike('fa.title', "%{$get['title']}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            // var_dump($get['create_at']);die;
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);

        }
        return parent::_list($db->order('create_at desc,id desc'));
    }

    public function _form_assign()
    {
        $apl = [1 => "任务问题", 2 => "好评问题", 3 => "淘宝客", 4 => "价格错误", 5 => "未下单/未付款/拍错店"];
        $this->assign(["apl" => $apl]);
    }


}
