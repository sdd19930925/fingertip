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
 * 平台管理
 * Class Goods
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Store extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'FingerStore';

    /**
     * 平台管理
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '店铺管理';
        $this->_form_assign();
        $get = $this->request->get();
        $uid = session("user.id");
        $db = Db::name($this->table)->alias("s")->field("s.*,p.cate_title,p.id as pid")->join("finger_platform p", "p.id=s.platform");
        if (isset($get['ty']) && $get['ty'] == 2) {//商户端
            $db->where(["uid" => $uid]);
        }
        if (isset($get['store_name']) && $get['store_name'] !== '') {//匹配店铺
            $db->whereLike('s.store_name', "%{$get['store_name']}%");
        }
        if (isset($get['store_num']) && $get['store_num'] !== '') {//匹配旺旺号
            $db->whereLike('s.store_num', "%{$get['store_num']}%");
        }
        if (isset($get['platform']) && $get['platform'] !== '') {//匹配平台
            $db->where('p.cate_title', "%{$get['platform']}%");
        }
        if (isset($get['tel']) && $get['tel'] !== '') {//匹配电话
            $db->whereLike('s.tel', "%{$get['tel']}%");
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
        $uid = session("user.id");
        $platform = db("finger_platform")->field("id,cate_title")->where(["pid" => 0])->select();
        $this->assign(["platform" => $platform, "uid" => $uid]);
    }

    /**
     * 添加平台
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function add()
    {
        $this->_form_assign();
        return $this->_form($this->table, 'form');

    }

    /**
     * 编辑平台
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function edit()
    {
        $this->_form_assign();
        return $this->_form($this->table, 'form');
    }

    /**
     * 删除平台
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("平台删除成功！", '');
        }
        $this->error("平台删除失败，请稍候再试！");
    }


}
