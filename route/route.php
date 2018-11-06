<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
	dump(time());
});

//依赖注入
// Route::get('hello/:name', 'index/hello');
Route::get('hello/:name', function (\think\Request $request, $name) {
	$method = $request->method();
	return '[' . $method . '] Hello, ' . $name;
});
//指定响应对象
// Route::get('test/:name', function (\think\Response $response, $name) {
// 	return $response->data('Hello, ' . $name)
// 		->code(200)
// 		->contentType('text/plain');
// });

//对资源文件的请求设置404访问
Route::get('static', function () {
	abort(404);
});

//域名绑定到模块,把二级域名api绑定到api模块
Route::domain('api', 'api');

Route::get('index', 'user/index');

//获取验证码的路由
Route::get('code/:time/:token/:username/:is_exist', 'code/get_code')->pattern(['username' => '[\w\.\@]+']);

Route::post('user/register', 'user/register');
Route::post('user/login', 'user/login');
Route::post('user/avatar', 'user/upload_head_img');
Route::post('user/change_pwd', 'user/change_pwd');
Route::post('user/reset_pwd', 'user/reset_pwd');
Route::post('user/bind_email', 'user/bind_email');
Route::post('user/nickname', 'user/set_nickname');

/******************* article *********************/
Route::post('article', 'article/add_article');
Route::get('articles/:time/:token/:user_id/[:num]/[:page]', 'article/article_list');
Route::get('article/:time/:token/:article_id', 'article/article_info');
Route::put('article', 'article/article_update');
Route::delete('article', 'article/article_del');

return [

];
