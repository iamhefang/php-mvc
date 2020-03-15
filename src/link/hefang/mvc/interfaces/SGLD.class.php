<?php


namespace link\hefang\mvc\interfaces;


use link\hefang\mvc\views\BaseView;

interface SGLD
{
	/**
	 * 添加或更新数据
	 * post: 添加数据
	 * put: 全量更新
	 * patch: 局部更新
	 * @method POST
	 * @method PUT
	 * @method PATCH
	 * @param string|null $id
	 * @return BaseView
	 */
	public function set(string $id = null): BaseView;

	/**
	 * 获取一条数据
	 * @method GET
	 * @param string|null $id 要获取的数据的id
	 * @return BaseView
	 */
	public function get(string $id = null): BaseView;

	/**
	 * 获取内容列表
	 * @method GET
	 * @param string|null $cmd 自定义参数
	 * @return BaseView
	 */
	public function list(string $cmd = null): BaseView;

	/**
	 * 删除一条数据
	 * @method DELETE
	 * @param string|null $cmd
	 * @return BaseView
	 */
	public function delete(string $cmd = null): BaseView;
}
