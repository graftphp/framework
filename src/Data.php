<?php

namespace GraftPHP\Framework;

class Data
{
    use MagicCall;

    public function append($item)
    {
        $this->{count((array) $this) + 1} = $item;
        return $this;
    }

    public function count()
    {
        return count((array) $this);
    }

    public function first()
    {
        return reset($this);
    }

    public function populateFunc($object)
    {
        foreach (get_object_vars($object) as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }
}
