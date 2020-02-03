<?php

namespace link\hefang\mvc\interfaces;


use link\hefang\mvc\views\BaseView;

/**
 * I(insert): 新建数据
 * D(delete): 删除一或多条数据
 * U(update): 更新一条数据
 * L(list): 获取符合条件的数据列表
 * G(get): 获取一条确定的数据
 * @package link\hefang\mvc\interfaces
 */
interface IDULG
{
	/**
	 * 添加数据
	 * @return BaseView
	 */
	public function insert(): BaseView;

	/**
	 * 删除数据
	 * @param string|null $id 删除主键为$id的数据
	 * @return BaseView
	 */
	public function delete(string $id = null): BaseView;

	/**
	 * 更新数据
	 * @param string|null $id 更新主键为$id的数据
	 * @return BaseView
	 */
	public function update(string $id = null): BaseView;

	/**
	 * 查询数据列表
	 * @return BaseView
	 */
	public function list(): BaseView;

	/**
	 * 获取一条数据详情
	 * @param string|null $id 要获取详情的数据的主键
	 * @return BaseView
	 */
	public function get(string $id = null): BaseView;
}
