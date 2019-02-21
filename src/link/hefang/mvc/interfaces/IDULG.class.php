<?php
/**
 * Created by IntelliJ IDEA.
 * User: hefang
 * Date: 2018/12/4
 * Time: 07:52
 */

namespace link\hefang\mvc\interfaces;


use link\hefang\mvc\views\BaseView;

interface IDULG
{
    /**
     * 添加数据
     * @return BaseView
     */
    public function insert(): BaseView;

    /**
     * 删除数据
     * @return BaseView
     */
    public function delete(): BaseView;

    /**
     * 更新数据
     * @return BaseView
     */
    public function update(): BaseView;

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