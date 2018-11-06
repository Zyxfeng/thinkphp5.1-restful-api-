<?php
namespace app\api\controller;
use PHPMailer\PHPMailer;
use think\facade\Session;

class Code extends Common {
	public function get_code() {
		//获取通过简单验证之后的用户提交数据
		$username = $this->params['username'];
		$exist = $this->params['is_exist'];

		$username_type = $this->check_username($username);
		switch ($username_type) {
		case 'email':
			$this->get_code_by_username($username, 'email', $exist);
			break;
		case 'phone':
			$this->get_code_by_username($username, 'phone', $exist);
			break;
		}
	}
	/**
	 * 手机号和邮箱获取验证码
	 * @param  string $username 用户提交的账号
	 * @param string $type [phone or email]
	 * @param  int    $exist 账号是否于数据库中存在
	 * @return json        api返回的json数据
	 */
	public function get_code_by_username($username, $type, $exist) {
		$type_name = $type == 'phone' ? '手机' : '邮箱';
		/******************* 检测账号是否存在 *******************/
		$this->check_exist($username, $type, $exist);
		/********************* 检测请求验证码的频率 30秒一次 ******/
		//开启session
		session([
			'prefix' => 'api',
			'type' => '',
			'auto_start' => true,
		]);
		if (Session::has($username . '_last_send_time')) {
			if (time() - session($username . '_last_send_time') < 30) {
				$this->return_msg(400, $type_name . '验证码每30秒才能请求一次');
			}
		}
		/************************ 生成验证码 **********************/
		$code = $this->make_code(6);
		/************************** 验证码存入session *************/
		$md5_code = md5($username . '_' . md5($code));
		session($username . '_code', $md5_code);
		session($username . '_last_send_time', time()); //把最后一次获得验证码的时间也存入session
		/*************************** 发送验证码 ******************/
		if ($type == 'phone') {
			$this->send_code_to_phone($username, $code);
		} else {
			$this->send_code_to_email($username, $code);
		}
	}
	/**
	 *
	 * @param  [int] $num 所需验证的长度
	 * @return [int]      指定长度的验证码
	 */
	public function make_code($num) {
		$max = pow(10, $num) - 1;
		$min = pow(10, $num - 1);
		/** 生成一个范围内内的一个随机数 */
		return rand($min, $max);
	}
	/** curl 调用赛邮发送短信验证码 */
	public function send_code_to_phone($phone, $code) {
		$curl = curl_init(); //初始化curl
		curl_setopt($curl, CURLOPT_URL, 'https://api.mysubmail.com/message/xsend');
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		$data = [
			'appid' => '15180',
			'to' => $phone,
			'project' => '9CTTG2',
			'vars' => '{"code":' . $code . ',"time": "60"}',
			'signature' => '76a9e82484c83345b7850395ceb818fb',
		];
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$res = curl_exec($curl);
		curl_close($curl);
		$res = json_decode($res);
		if ($res->status != 'success') {
			$this->return_msg(400, $res->msg);
		} else {
			$this->return_msg(200, '手机验证码发送成功, 请在1分钟内验证，每天只会发送5次');
		}

	}
	/**
	 * 发送邮件验证码
	 * @param  [string] $email [客户端提交的邮箱地址]
	 * @param  [string] $code  [生成的指定位数验证码]
	 * @return [json]        [description]
	 */
	public function send_code_to_email($email, $code) {
		$mail = new PHPMailer(true);
		try {
			//Server settings
			$toemail = $email;
			// $mail->SMTPDebug = 2; //enable verbose debug output
			$mail->isSMTP();
			$mail->CharSet = 'utf8';
			$mail->Host = 'smtp.qq.com';
			$mail->SMTPAuth = true;
			$mail->Username = '1143677326@qq.com';
			$mail->Password = 'wtmzdxvyqculhcfj';
			$mail->SMTPSecure = 'tls';
			$mail->Port = 587;

			$mail->setFrom('1143677326@qq.com', '接口测试');
			$mail->addAddress($toemail, 'test');
			$mail->addReplyTo('1143677326@qq.com', 'Reply');

			//Content
			$mail->isHTML(true);
			$mail->Subject = '你有新的验证码';
			$mail->Body = '这是一个测试邮件，你的验证码是' . $code . ', 请勿回复此邮件';

			$mail->send();
			// echo 'Message has been sent';
			$this->return_msg(200, '邮件发送成功!请注意查收');
		} catch (\Exception $e) {
			// echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
			$this->return_msg(400, $mail->ErrorInfo);
		}
	}

}

?>