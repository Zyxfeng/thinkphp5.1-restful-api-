<?php
namespace app\api\controller;

/**
 * summary
 */
class Article extends Common {
	/**
	 * summary
	 */
	public function add_article() {
		/** 接收参数 */
		$data = $this->params;
		$data['article_ctime'] = time();
		/** 写入数据库 */
		$res = db('article')->insertGetId($data);
		if ($res) {
			$this->return_msg(200, '新增文章成功!', $res);
		} else {
			$this->return_msg(400, '新增文章失败!');
		}
	}
	/**
	 * 文章列表
	 * @return [type] [description]
	 */
	public function article_list() {
		/** @var data 接收参数 */
		$data = $this->params;
		if (!isset($data['num'])) {
			$data['num'] = 10;
		}
		if (!isset($data['page'])) {
			$data['page'] = 1;
		}
		/** 查询数据库 */
		$where['article_uid'] = $data['user_id'];
		/** @var int 总条数 */
		$count = db('article')->where($where)->count();
		/** @var int 总页数 */
		$page_total = ceil($count / $data['num']);
		$field = 'article_id, article_title, article_ctime, user_name';
		/** @var array 联表查询的条件 */
		$join = [['api_user u', 'u.user_id = a.article_uid']];
		$res = db('article')
			->alias('a')
			->field($field)
			->where($where)
			->join($join)
			->order('article_ctime desc')
			->page($data['page'], $data['num'])
			->select();
		/** @var mixin 判断结果 */
		if ($res === false) {
			$this->return_msg(400, '查询失败');
		} else if (empty($res)) {
			$this->return_msg(200, '暂无数据');
		} else {
			$return_data['articles'] = $res;
			$return_data['page_total'] = $page_total;
			$this->return_msg(200, '查询成功', $return_data);
		}
	}

	public function article_info() {
		/** 接收参数 */
		$data = $this->params;
		$field = 'article_id, article_title, article_ctime, article_content, user_name';
		$join = [['api_user u', 'u.user_id = a.article_uid']];
		$res = db('article')
			->alias('a')
			->field($field)
			->where('article_id', $data['article_id'])
			->join($join)
			->find();
		/** 判断结果 */
		if (!$res) {
			$this->return_msg(400, '查询失败');
		} else {
			$res['article_content'] = htmlspecialchars_decode($res['article_content']);
			$this->return_msg(200, '查询成功', $res);
		}
	}
	public function article_update() {
		/** 接收参数 */
		$data = $this->params;
		$res = db('article')->update($data);
		if ($res !== false) {
			$this->return_msg(200, '更新文章成功');
		} else {
			$this->return_msg(400, '更新文章失败');
		}
	}
	public function article_del() {
		/**接收参数 */
		$data = $this->params;
		$res = db('article')->delete($data['article_id']);
		if ($res) {
			$this->return_msg(200, '删除文章成功');
		} else {
			$this->return_msg(400, '删除文章失败');
		}
	}
}
?>