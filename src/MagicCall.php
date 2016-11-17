<?php

namespace GraftPHP\Framework;

trait magicCall {

    public function __call($method, $args) {
        if (method_exists(get_class(), $method . '_func')) {
            return $this->{$method . '_func'}(...$args);
        }
        die($method . ' method not found');
    }

    static public function __callStatic($method, $args) {
        if (method_exists(get_class(), $method . '_func')) {
            $o = new static();
            return $o->{$method . '_func'}(...$args);
        }
        die($method . ' method not found');
    }

}
