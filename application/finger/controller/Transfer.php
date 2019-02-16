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
 * 提现管理
 * Class Goods
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Transfer extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'FingerTransfer';

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
        $this->title = '转账管理';

        $get = $this->request->get();
        $db = Db::name($this->table)
            ->alias("fc")
            ->field("fc.*,su.username")
            ->join("system_user su", "su.id=fc.uid");
        if (isset($get['tr_name']) && $get['tr_name'] !== '') {//匹配真实姓名
            $db->whereLike('fc.tr_name', $get['tr_name']);
        }
        if (isset($get['username']) && $get['username'] !== '') {//匹配商户
            $db->whereLike('su.username', $get['username']);
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            // var_dump($get['create_at']);die;
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('fc.create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);

        }
        return parent::_list($db->order('fc.create_at desc,fc.id desc'));
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
        $this->assign("uid", session("user.id"));
        return $this->_form($this->table, 'form');
    }

    /** 导出会员卡
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
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
        $xlsData = Db::name('finger_cash')
            ->alias("mc")
            ->field("mc.*,ma.nickname")
            ->join("finger_user ma", "ma.id=mc.mid")
            ->where(["mc.exp" => 1])
            ->select();
        db("finger_cash")->where(["exp" => 1])->update(["exp" => 2]);

        $a = exportExcel($xlsName, $xlsCell, $xlsData);
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
    public function handle()
    {
        if (!$this->request->post()) {
            $t_id = $this->request->get('id');
            $task = Db::name('FingerTransfer')->field("id,handle")->where(['id' => $t_id])->find();
            return $this->fetch('examine', ['vo' => $task]);
        }
        // 入库保存
        list($post, $data) = [$this->request->post(), []];

        if (Db::name('FingerTransfer')->update($post) !== false) {
            $d = Db::name("FingerTransfer")->field("id,uid,h_money")->where("id", $post['id'])->find();
            if ($post['handle'] == 2) {
                Db::name("SystemUser")->where(["id" => $d['uid']])->update(["principal" => Db::raw("principal+" . $d['h_money'])]);
            }

            $this->success('状态处理成功！', '');
        }
        $this->error('系统故障！');
    }

}
