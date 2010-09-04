<?php

namespace qeephp\storage;

/**
 * 模型基础类，提供一些常用功能的实现
 */
abstract class BaseModel implements IStorageDefine
{
    /**
     * 指示模型对象实例是否是新创建的
     *
     * @var bool
     * @internal
     */
    public $__is_dirty = true;

    /**
     * 对象实例从存储服务中读取出来时的值
     *
     * @var array
     * @internal
     */
    public $__props = array();

    /**
     * 改动过的属性值
     *
     * @var array
     * @internal
     */
    public $__changes = array();

    /**
     * 返回模型的主键值，如果有多个主键，则返回包含多个主键的数组
     *
     * @return mixed
     */
    function id()
    {
        $idname = static::meta()->idname;
        if (!is_array($idname)) return $this->$idname;
        $id = array();
        foreach ($idname as $name)
        {
            $id[$name] = $this->$name;
        }
        return $id;
    }

    /**
     * 指示模型是否未写入缓存
     *
     * @return bool
     */
    function is_dirty()
    {
        return $this->__is_dirty;
    }

    /**
     * 指示模型属性是否已经发生改变
     *
     * @return bool
     */
    function is_changed()
    {
        return !empty($this->__changes);
    }

    /**
     * 返回模型已经改动过的属性
     *
     * @return array
     */
    function changes()
    {
        return $this->__changes;
    }

    /**
     * 取消模型属性的修改
     *
     * @return array
     */
    function revert()
    {
        $this->__changes = array();
    }

    /**
     * 返回所有属性未被修改前的值
     *
     * @retrun array
     */
    function original()
    {
        return $this->__props;
    }

    /**
     * 返回模型属性组成的数组
     *
     * @return array
     */
    function __to_array()
    {
        $meta = static::meta();
        /* @var $meta Meta */
        $arr = array();
        foreach ($meta->props as $name => $prop)
        {
            $arr[$name] = $this->$name;
        }
        return $arr;
    }

    /**
     * 返回模型的 Meta 对象
     *
     * @return Meta
     */
    function my_meta()
    {
        return Meta::instance(get_class($this));
    }

    /**
     * 返回模型类的 Meta 对象
     *
     * @return Meta
     */
    static function meta()
    {
        return Meta::instance(get_called_class());
    }

    /**
     * 按照指定条件查询一个模型实例
     *
     * @param mixed $cond
     *
     * @return BaseModel
     */
    static function find_one($cond)
    {
        return Repo::find_one(get_called_class(), func_get_args());
    }

    /**
     * 返回一个 IAdapterFinder 对象
     *
     * @param mixed $cond
     * 
     * @return qeephp\interfaces\IAdapterFinder
     */
    static function find($cond)
    {
        return Repo::find(get_called_class(), func_get_args());
    }

    /**
     * 保存对象
     *
     * @return mixed
     */
    function save()
    {
        return $this->is_dirty() ? Repo::create($this) : Repo::update($this);
    }

    /**
     * 删除对象，成功返回 true
     *
     * @return bool
     */
    function del()
    {
        return Repo::del($this);
    }

    /**
     * 删除符合条件的对象，返回被删除对象的总数
     *
     * @param mixed $cond
     *
     * @return int
     */
    static function del_by($cond)
    {
        return Repo::del_by(get_called_class(), func_get_args());
    }

    /**
     * 处理对象属性的读取
     *
     * @param string $prop
     *
     * @return mixed
     */
    function __get($prop)
    {
        if (array_key_exists($prop, $this->__changes))
        {
            return $this->__changes[$prop];
        }
        if (array_key_exists($prop, $this->__props))
        {
            return $this->__props[$prop];
        }

        $meta = static::meta();
        /* @var $meta Meta */
        if (!isset($meta->props[$prop]))
        {
            throw StorageError::unknown_model_prop_error($meta->class, $prop);
        }
        throw StorageError::incomplete_model_prop_error($meta->class, $prop);
    }

    /**
     * 修改对象属性
     *
     * @param string $prop
     * @param mixed $value
     */
    function __set($prop, $value)
    {
        $this->__changes[$prop] = $value;
    }

    function __read(array $props)
    {
        foreach ($props as $prop => $value)
        {
            unset($this->$prop);
        }
        $this->__is_dirty = false;
        $this->__props = $props;
        $this->__changes = array();
    }

    function __save($is_create, $id = null)
    {
        $this->__is_dirty = false;
        $meta = $this->my_meta();
        $this->__props = array_merge($this->__props, $this->__changes);
        $this->__changes = array();
        if ($is_create)
        {
            if (is_array($meta->composite_id))
            {
                $this->__props = array_merge($this->__props, $id);
            }
            else
            {
                $this->__props[$meta->idname] = $id;
            }
        }
    }

    function __call($method, array $args)
    {
        $meta = static::meta();
        /* @var $meta Meta */
        if (isset($meta->dyanmic_methods[$method]))
        {
            $callback = $meta->dyanmic_methods[$method];
            array_unshift($args, $this);
            return call_user_func_array($callback, $args);
        }
        else
        {
            // bug in PHP 5.3 - PHP 5.3.2
            // http://bugs.php.net/bug.php?id=51176
            // __call override __callStatic
            if (isset($meta->static_methods[$method]))
            {
                $callback = $meta->static_methods[$method];
                return call_user_func_array($callback, $args);
            }
            throw StorageError::not_implemented_error(get_called_class() . '::' . $method);
        }
    }

    static function __callStatic($method, array $args)
    {
        $meta = static::meta();
        /* @var $meta Meta */
        if (isset($meta->static_methods[$method]))
        {
            $callback = $meta->static_methods[$method];
            return call_user_func_array($callback, $args);
        }
        throw StorageError::not_implemented_error(get_called_class() . '::' . $method);
    }
}
