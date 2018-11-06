<?php
namespace app\api\controller;

/**
 * summary
 */
class User extends Common {
	public function index() {
		// halt();
		return json(['code' => 200, 'data' => ['name' => 'xiaoming', 'age' => 19], 'msg' => '请求成功']);
	}
	/**
	 * 用户注册函数
	 * @return json [是否注册成功的json数据]
	 */
	public function register() {
		/** @var array 接收参数 */

		$data = $this->params;
		/** 检查验证码 */
		$this->check_code($data['user_name'], $data['code']);

		/** 检测用户名 */
		$user_name_type = $this->check_username($data['user_name']);

		switch ($user_name_type) {
		case 'phone':
			$this->check_exist($data['user_name'], 'phone', 0);
			$data['user_phone'] = $data['user_name'];
			break;

		case 'email':
			$this->check_exist($data['user_name'], 'email', 0);
			$data['user_email'] = $data['user_name'];
			break;
		}
		/** 将用户的注册信息写入数据库中 */
		unset($data['user_name']);
		$data['user_regtime'] = time();
		$re = db('user')->insert($data);
		if (!$re) {
			$this->return_msg(400, '用户注册失败!');
		} else {
			$this->return_msg(200, '用户注册成功!', $re);
		}

	}
	/**
	 * 用户登录的方法
	 * @return json api返回的json数据
	 */
	public function login() {
		/** @var data 接收参数 */
		$data = $this->params;
		/** 检测用户名 */
		$user_name_type = $this->check_username($data['user_name']);
		/** 分别对手机号和邮箱做不同的处理 */
		switch ($user_name_type) {
		case 'phone':
			$this->check_exist($data['user_name'], 'phone', 1);
			$db_res = db('user')
				->field('user_id,user_name,user_phone,user_email,user_regtime,user_pwd')
				->where('user_phone', $data['user_name'])
				->find();
			break;

		case 'email':
			$this->check_exist($data['user_name'], 'email', 1);
			$db_res = db('user')
				->field('user_id,user_name,user_phone,user_email,user_regtime,user_pwd')
				->where('user_email', $data['user_name'])
				->find();
			break;
		}
		/** 检测密码是否正确 */
		if ($db_res['user_pwd'] !== $data['user_pwd']) {
			$this->return_msg(400, '密码不正确');
		}
		unset($db_res['user_pwd']);
		$this->return_msg(200, '登陆成功', $db_res);
	}
	public function upload_head_img() {
		/** 接收参数 */
		$data = $this->params;
		/** 上传头像 */
		$head_img_path = $this->upload_file($data['user_avatar'], 'head_img');
		/** 写入数据库 */
		$res = db('user')->where('user_id', $data['user_id'])->setField('user_avatar', $head_img_path);
		if (!$res) {
			$this->return_msg(400, '上传头像失败');
		} else {
			$this->return_msg(200, '上传头像成功', $head_img_path);
		}
	}
	public function change_pwd() {
		/** 接收参数 */
		$data = $this->params;
		/** 检查用户名 */
		$user_name_type = $this->check_username($data['user_name']);
		switch ($user_name_type) {
		case 'phone':
			$this->check_exist($data['user_name'], 'phone', 1);
			$where['user_phone'] = $data['user_name'];
			break;
		case 'email':
			$this->check_exist($data['user_name'], 'email', 1);
			$where['user_email'] = $data['user_name'];
			break;
		}
		/** 验证原来的密码 */
		$db_pwd = db('user')
			->where($where)
			->value('user_pwd');
		if ($data['user_ini_pwd'] !== $db_pwd) {
			$this->return_msg(400, '原始密码错误');
		}
		/** 对比新的密码 */

		/** 把新密码存入数据库 */
		$res = db('user')->where($where)->setField('user_pwd', $data['user_pwd']);
		if ($res !== false) {
			$this->return_msg(200, '修改密码成功');
		} else {
			$this->return_msg(400, '修改密码失败');
		}
	}
	/** 用户找回密码的接口 */
	public function reset_pwd() {
		/** 接收参数 */
		$data = $this->params;
		/** 检查验证码 */
		$this->check_code($data['user_name'], $data['code']);
		/** 检查用户名 */
		$user_name_type = $this->check_username($data['user_name']);
		switch ($user_name_type) {
		case 'phone':
			$this->check_exist($data['user_name'], 'phone', 1);
			$where['user_phone'] = $data['user_name'];
			break;
		case 'email':
			$this->check_exist($data['user_name'], 'email', 1);
			$where['user_email'] = $data['user_name'];
			break;
		}
		/** 新密码写入数据库 */
		$res = db('user')->where($where)->setField('user_pwd', $data['user_pwd']);
		if ($res !== false) {
			$this->return_msg(400, '修改密码成功');
		} else {
			$this->return_msg(400, '修改密码失败');
		}
	}
	/** 绑定邮箱的接口 */
	public function bind_email() {
		/** 接收参数 */
		$data = $this->params;
		/** 检查验证码 */
		$this->check_code($data['email'], $data['code']);
		/** 把用户邮箱写入数据库 */
		$res = db('user')
			->where('user_id', $data['user_id'])
			->setField('user_email', $data['email']);
		if ($res !== false) {
			$this->return_msg(200, '绑定邮箱成功');
		} else {
			$this->return_msg(400, '绑定邮箱失败');
		}
	}
	public function set_nickname() {
		/** 接收参数 */
		$data = $this->params;
		/** 昵称写入数据库 */
		$res = db('user')
			->where('user_id', $data['user_id'])
			->setField('user_name', $data['user_name']);
		if (!$res) {
			$this->return_msg(400, '修改昵称失败');
		} else {
			$this->return_msg(200, '修改昵称成功');
		}
	}
}
?>