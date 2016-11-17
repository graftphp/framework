<?php

namespace GraftPHP\Framework;

Class Data
{

	public function __call($method, $args)
	{
		switch ($method) {
			case 'populate' :
				return $this->populate_func(...$args);
				break;
		}
	}

	static public function __callStatic($method, $args)
	{
		switch ($method) {
			case 'populate' :
				$o = new static();
				return $o->populate_func(...$args);
				break; 
		}
        die($method . ' method not found');
	}

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
