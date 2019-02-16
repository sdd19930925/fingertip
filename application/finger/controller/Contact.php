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
 * 商户管理
 * Class Goods
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Contact extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'SystemUser';

    /**
     * 商户管理
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '商户管理';
        $get = $this->request->get();
        $db = Db::name($this->table)->where(['is_deleted' => '0', "level" => 3]);
        if (isset($get['username']) && $get['username'] !== '') {
            $db->whereLike('username', "%{$get['username']}%");
        }
        if (isset($get['phone']) && $get['phone'] !== '') {
            $db->whereLike('phone', "%{$get['phone']}%");
        }
        if (isset($get['qq']) && $get['qq'] !== '') {
            $db->whereLike('qq', "%{$get['qq']}%");
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            // var_dump($get['create_at']);die;
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);

        }
        return parent::_list($db->order('create_at desc,id desc'));
    }

    /**
     * 编辑商户
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
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
            $user = Db::name('SystemUser')->field("id,examine")->where(['id' => $t_id])->find();
            return $this->fetch('', ['vo' => $user]);
        }
        // 入库保存
        list($post, $data) = [$this->request->post(), []];

        if (Db::name('SystemUser')->update($post) !== false) {
            $this->success('状态处理成功！', '');
        }
        $this->error('系统故障！');
    }


}
