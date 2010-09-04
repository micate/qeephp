<?php

namespace qeephp\mvc;

/**
 * 动作对象基础类
 */
abstract class BaseAction
{
    /**
     * 应用程序对象
     *
     * @var App
     */
    public $app;

    /**
     * 动作名称
     *
     * @var string
     */
    public $name;

    /**
     * 执行结果
     *
     * @var mixed
     */
    public $result;

    /**
     * 构造函数
     *
     * @param App $app
     * @param string $name
     */
    function __construct(App $app, $name)
    {
        $this->app = $app;
        $this->name = $name;
    }

    /**
     * 执行动作
     */
    function execute()
    {
        if (!$this->_before_execute()) return;
        $this->result = $this->_execute();
        $this->_after_execute();
    }

    /**
     * 取得指定的视图对象
     *
     * @param array $vars
     *
     * @return View
     */
    function view(array $vars = null)
    {
        if (!is_array($vars)) $vars = array();
        return $this->app->view($this->name, $vars);
    }

    /**
     * 应用程序执行的动作内容，在继承的动作对象中必须实现此方法
     * 
     * 返回值会被保存到动作对象的 $result 属性中。
     *
     * @return mixed
     */
    abstract protected function _execute();

    /**
     * 执行动作之前调用，如果返回 false 则阻止动作的执行
     *
     * @return bool
     */
    protected function _before_execute()
    {
        return true;
    }

    /**
     * 执行动作之后调用
     */
    protected function _after_execute()
    {
    }
}
