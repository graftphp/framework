<?php

namespace GraftPHP\Framework;

trait MagicCall
{
    public function __call($method, $args)
    {
        if (method_exists(get_class(), $method . 'Func')) {
            return $this->{$method . 'Func'}(...$args);
        }
        die($method . ' method not found');
    }

    static public function __callStatic($method, $args)
    {
        if (method_exists(get_class(), $method . 'Func')) {
            $o = new static();
            return $o->{$method . 'Func'}(...$args);
        }
        die($method . ' method not found');
    }
}
