<?php

namespace GraftPHP\Framework;

Class Data
{

    use MagicCall;

    public function append($o)
    {
        $this->{ count((array)$this)+1 } = $o;
        return $this;
    }

    public function count()
    {
        return count((array)$this);
    }

    public function first()
    {
        return reset($this);
    }

    public function populate_func($o)
    {
        foreach (get_object_vars($o) as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

}
