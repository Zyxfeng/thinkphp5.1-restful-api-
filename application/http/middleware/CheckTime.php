<?php

namespace app\http\middleware;

class CheckTime {
	public function handle($request, \Closure $next) {

		$response = $next($request);

		if (!isset($request->param['time']) || intval($request->param['time']) <= 1) {
			echo \json_encode(['code' => 400, 'msg' => '时间戳不正确', 'data' => []]);
		}

		return $response;
	}
}
