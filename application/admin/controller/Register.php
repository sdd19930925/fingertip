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

namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\NodeService;
use think\Db;
use think\facade\Validate;


/**
 * 系统登录控制器
 * class Login
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/10 13:59
 */
class Register extends BasicAdmin
{

//    /**
//     * 控制器基础方法
//     */
//    public function initialize()
//    {
//        if (session('user.id') && $this->request->action() !== 'out') {
//            $this->redirect('@admin');
//        }
//    }

    /**
     * 用户登录
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetch('', ['title' => '用户注册']);
        }
        // 输入数据效验
        $validate = Validate::make([
            'username' => 'require|min:4',
            'password' => 'require|min:4',
            'qq' => 'require|min:9',
            'phone' => 'require|min:11',
        ], [
            'username.require' => '登录账号不能为空！',
            'username.min' => '登录账号长度不能少于4位有效字符！',
            'password.require' => '登录密码不能为空！',
            'password.min' => '登录密码长度不能少于4位有效字符！',
            'qq.require' => 'qq不能为空！',
            'qq.min' => 'qq长度长度不能少于9位有效字符！',
            'phone.require' => '手机号不能为空！',
            'phone.min' => '手机号长度长度不能少于11位有效字符！',
        ]);
        $data = [
            'username' => $this->request->post('username', ''),
            'password' => md5($this->request->post('password', '')),
            'qq' => $this->request->post('qq', ''),
            'phone' => $this->request->post('tel', ''),
        ];
        $validate->check($data) || $this->error($validate->getError());
        // 用户信息验证
        $user = Db::name('SystemUser')->where(['username' => $data['username'], 'is_deleted' => '0'])->find();
        !empty($user) && $this->error('账号已存在，请重新输入!');
        // 更新登录信息
        session('user', $user);
        !empty($user['authorize']) && NodeService::applyAuthNode();
        LogService::write('系统管理', '用户注册系统成功');
        $invite = $this->request->post('invite', 0);
        if (isset($invite) && $invite != "0") {
            $data['invite'] = $invite;
        }
        $data['authorize'] = 3;
        if (Db::name('SystemUser')->insertGetId($data)) {
            $this->success('注册成功，正在进入系统...', url('@admin/login'));
        } else {
            $this->error('系统故障', url('@admin/register'));
        }

    }

}
