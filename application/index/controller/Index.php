<?php
namespace app\index\controller;

class Index {
	public function index() {
		return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">12载初心不改（2006-2018） - 你值得信赖的PHP框架</span></p></div><think id="eab4b9f840753f8e7"></think>';
	}

	public function hello($name = 'ThinkPHP5') {
		return 'hello, ' . $name;
	}
	/**
	 * 使用注解路由，只需在控制器类的方法注释中定义
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 * @route('annotation/:name')
	 */
	public function annotation($name) {
		return 'Hello, ' . $name;
	}
}
