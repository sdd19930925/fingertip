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

namespace app\merchants\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;

/**
 * 提现管理
 * Class Goods
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Credit extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'FingerCredit';

    /**
     * 提现管理
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '提现管理';

        $get = $this->request->get();
        $db = Db::name($this->table)
            ->alias("fc")
            ->field("fc.*,fu.username")
            ->join("system_user fu", "fu.id=fc.uid");
        if (isset($get['num']) && $get['num'] !== '') {
            $db->whereLike('fc.serial_number', "%{$get['num']}%");
        }
        if (isset($get['name']) && $get['name'] !== '') {//匹配店铺
            $db->where('fu.username', $get['name']);
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            // var_dump($get['create_at']);die;
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('fc.create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);

        }
        return parent::_list($db->order('fc.create_at desc,fc.id desc'));
    }


    /**
     * 添加提现
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function add()
    {
        $uid = session("user.id");
        $this->assign("uid", $uid);
        return $this->_form($this->table, 'form');

    }

    /**
     * 编辑提现
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function edit()
    {
        $uid = session("user.id");
        $this->assign("uid", $uid);
        return $this->_form($this->table, 'form');
    }

    /**
     * 删除提现
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("提现删除成功！", '');
        }
        $this->error("提现删除失败，请稍候再试！");
    }

    /**
     * 导出会员卡
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function daochu()
    {
        $xlsName = "会员提现记录";
        $xlsCell = array(
            array('id', "编号"),
            array('serial_number', "流水号"),
            array('tel', '电话'),
            array('username', '提现人'),
            array('number', "身份证号"),
            array('opening_bank', "开户行"),
            array('nom', "银行卡号"),
            array('h_money', "提现金额"),
            array('create_at', "时间"),


        );
        $xlsData = Db::name('FingerCash')
            ->alias("mc")
            ->field("mc.*,ma.nickname")
            ->join("finger_user ma", "ma.id=mc.mid")
            ->where(["mc.exp" => 1])
            ->select();
        Db::name("FingerCash")->where(["exp" => 1])->update(["exp" => 2]);

        $a = exportExcel($xlsName, $xlsCell, $xlsData);
    }

}
