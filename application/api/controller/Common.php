<?php
namespace app\api\controller;
use \think\Controller;
use \think\facade\Session;
use \think\Validate;

class Common extends Controller {
	protected $request;
	protected $validater; //用于验证数据
	protected $params; //过滤后符合要求的参数
	/** 定义验证规则 */
	protected $rules = array(
		'User' => array(
			'index' => array(
				'user_name|用户名' => ['require', 'chsDash', 'max' => 20],
				'user_pwd|用户密码' => 'require|length:32',
			),
			'register' => array(
				'user_name|手机号或邮箱' => 'require',
				'user_pwd|用户密码' => 'require|length:32',
				'code|验证码' => 'require|number|length:6',
			),
			'login' => array(
				'user_name|手机号或邮箱' => 'require',
				'user_pwd|用户密码' => 'require|length:32',
			),
			'upload_head_img' => array(
				'user_id' => 'require|number',
				'user_avatar' => 'require|image|fileSize:2000000|fileExt:jpg,png,bmg,jpeg',
			),
			'change_pwd' => array(
				'user_name' => 'require',
				'user_ini_pwd' => 'require|length:32',
				'user_pwd' => 'require|length:32',
			),
			'reset_pwd' => array(
				'user_name' => 'require',
				'user_pwd' => 'require|length:32',
				'code' => 'require|number|length:6',
			),
			'bind_email' => array(
				'user_id' => 'require|number',
				'code' => 'require|number|length:6',
				'email' => 'require|email',
			),
			'set_nickname' => array(
				'user_id|用户id' => 'require|number',
				'user_name|用户昵称' => 'require|chsDash|unique:user',
			),
		),
		'Code' => array(
			'get_code' => array(
				'username' => 'require',
				'is_exist' => 'require|number|length:1',
			),
		),
		'Article' => array(
			'add_article' => array(
				'article_uid|作者id' => 'require|number',
				'article_title|标题' => 'require|chsDash',
			),
			'article_list' => array(
				'user_id|用户id' => 'require|number',
				'num|每页总数' => 'number',
				'page|页码' => 'number',
			),
			'article_info' => array(
				'article_id|文章id' => 'require|number',
			),
			'article_update' => array(
				'article_id|文章id' => 'require|number',
				'article_tile|文章标题' => 'chsDash',
			),
			'article_del' => array(
				'article_id|文章id' => 'require|number',
			),
		),
	);

	protected function initialize() {
		parent::initialize();
		$this->request = request();
		//验证时间戳
		// $this->check_time($this->request->only(['time']));
		//验证token
		// $this->check_token($this->request->param());
		//验证之后去除time和token以外的参数
		// $this->params = $this->check_params($this->request->except(['time', 'token']));
		$this->params = $this->check_params($this->request->param(true));
	}
	/**
	 * 验证请求是否超时
	 * @param  [array] $arr [包含时间戳的参数数组]
	 * @return [json]      [检测结果]
	 */
	public function check_time($arr) {
		if (!isset($arr['time']) || intval($arr['time']) <= 1) {
			$this->return_msg(400, '时间戳不正确');
		}
		if (time() - intval($arr['time']) > 60) {
			// $this->return_msg(400, '请求超时');
		}
	}

	/**
	 * 验证token是否正确的函数
	 * @param  array $arr 客户端传递的所有参数
	 * @return void
	 */
	public function check_token($arr) {
		if (!isset($arr['token']) || empty($arr['token'])) {
			$this->return_msg(400, 'token不能为空');
		}
		//接收客户端传递过来的token
		$app_token = $arr['token'];
		//服务器构造一个token
		unset($arr['token']);
		$service_token = '';
		foreach ($arr as $value) {
			$service_token .= md5($value);
		}
		$service_token = md5('api_' . md5($service_token) . '_api');
		//对比token
		if ($app_token !== $service_token) {
			$this->return_msg(400, 'token值不正确');
		}
	}

	public function check_params($arr) {
		/** 获取验证规则 */
		// halt(request()->action());
		$rule = $this->rules[$this->request->controller()][$this->request->action()];
		/** 验证参数并获取错误时的返回 */
		$this->validater = new Validate($rule);
		if (!$this->validater->check($arr)) {
			$this->return_msg(400, $this->validater->getError());
		}
		/** @var array 验证通过保存参数 */
		return $arr;
	}
	/**
	 * 检测用户名并返回传入用户名的类别
	 * @param  string $username 提交的用户名
	 * @return string           检测的结果
	 */
	public function check_username($username) {

		$is_email = $this->validater->is($username, 'email') ? 1 : 0;

		$is_phone = preg_match('/^1[345678]\d{9}$/', $username) ? 4 : 2;

		$flag = $is_email + $is_phone;
		switch ($flag) {
		/*************************not email not phone **************/
		case 2:
			$this->return_msg(400, '邮箱或手机号不正确');
			break;
		/************************** is email not phone *************/
		case 3:
			return 'email';
			break;
		/************************** is phone not email ***************/
		case 4:
			return 'phone';
			break;
		}
	}

	public function check_exist($value, $type, $exist) {
		$type_num = $type == 'phone' ? 2 : 4;
		$flag = $type_num + $exist;
		$phone_res = db('user')->where('user_phone', $value)->find();
		$email_res = db('user')->where('user_email', $value)->find();
		switch ($flag) {
		/********************* 2+0 phone aleary exist exist in database************/
		case 2:
			if ($phone_res) {
				$this->return_msg(400, '此手机号已被占用');
			}
			break;
		/********************* 2+1 phone not exist in database *********************/
		case 3:
			if (!$phone_res) {
				$this->return_msg(400, '此手机号不存在');
			}
			break;
		/********************* 4+0 email aleary exist in database *********************/
		case 4:
			if ($email_res) {
				$this->return_msg(400, '此邮箱已被占用');
			}
			break;
		/********************* 4+1 email not exist in database *********************/
		case 5:
			if (!$email_res) {
				$this->return_msg(400, '此邮箱不存在');
			}
			break;
		}
	}
	/**
	 * 检查验证码的方法
	 * @param  string $user_name 提交的用户名
	 * @param  int $code       用户提交的验证码
	 * @return json            api返回的数据
	 */
	public function check_code($user_name, $code) {
		session([
			'prefix' => 'api',
			'type' => '',
			'auto_start' => true,
		]);
		if (Session::has($user_name . '_last_send_time')) {
			/** 检查是否超时 */
			if (time() - session($user_name . '_last_send_time') > 600) {
				$this->return_msg(400, '验证码已经过期,请重新获取!');
			}
			/** 检查验证码是否正确, 比对MD5之后的哈希值 */
			$md5_code = md5($user_name . '_' . md5($code));
			if (session($user_name . '_code') !== $md5_code) {
				$this->return_msg(400, '验证不正确!');
			}
			/** 不管验证码是否正确，都应该清楚已经验证过的验证码 */
			session($user_name . '_code', null);
		} else {
			$this->return_msg(400, '请先获取验证码');
		}
	}
	public function upload_file($file, $type = '') {
		$info = $file->rule('uniqid')->move('./uploads/');
		if ($info) {
			$path = './uploads/' . $info->getSaveName();
			/** 裁剪图片 */
			if (!empty($type)) {
				$this->image_edit($path, $type);
			}
		} else {
			$this->return_msg(400, $file->getError());
		}
		return realpath($path);
	}
	public function image_edit($path, $type) {
		$image = \think\Image::open($path);
		$filename = basename($path);
		switch ($type) {
		case 'head_img':
			$image->thumb(200, 200, \think\Image::THUMB_CENTER)->save('./uploads/' . 'thumb_' . $filename);
			break;
		}
	}
	/**
	 * 返回的结果信息
	 * @param  int $code 返回的结果码
	 * @param  string $msg  返回结果的提示信息
	 * @param  array  $data 返回的结果的数据数组
	 * @return json         最终的json数据
	 */
	public function return_msg($code, $msg = '', $data = []) {
		/** 组合数据 */
		$return_data['code'] = $code;
		$return_data['msg'] = $msg;
		$return_data['data'] = $data;
		/** 返回信息并终止脚本 */
		echo \json_encode($return_data);die;
	}
}
?>