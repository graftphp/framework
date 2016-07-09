<?php

namespace GraftPHP\Framework;

class Model
{

    static public function all($sortcol = null, $sortdir = null)
    {
        $obj = new static;
        $obj->build();
        $db = new DB();
        return $db->table(static::$db_tablename)
            ->get('*', $sortcol, $sortdir);
    }

    static public function build() {
        $obj = new static;
        $obj->updateTable();
        $obj->defaultData();
    }

    public function defaultData()
    {
        return true;
        if (isset(static::$db_defaultdata)) {
            foreach(static::$db_defaultdata as $k => $d) {
                $obj = new static;
                $obj->{static::$db_idcolumn} = ($k+1);
                foreach(static::$db_columns as $ci => $c) {
                    $obj->{$c[0]} = $d[$ci];
                }
                $obj->save();
            }
        }
    }

    public function delete()
    {
        if ($this->{static::$db_idcolumn}) {
            DB::delete(
                static::$db_tablename,
                static::$db_idcolumn,
                $this->{static::$db_idcolumn}
            );
        } else {
            dd("id not set");
        }
    }

    // find and return a single instance of the object
    static public function find($val, $column = null)
    {
        $col = $column ? $column : static::$db_idcolumn;
        $db = new DB();
        $res = $db->table(static::$db_tablename)
            ->where($col, '=', $val)
            ->first();
        $obj = new static;
        $obj->{static::$db_idcolumn} = $res[static::$db_idcolumn];
        foreach(static::$db_columns as $col) {
            $obj->{$col[0]} = $res[$col[0]];
        }
        return $obj;
    }

    static public function first()
    {
        $db = new DB();
        return $db->table(static::$db_tablename)
            ->first('*', static::$db_idcolumn, 'DESC');
    }

    public function sort($column,$direction)
    {
        dd($column);
    }

    public function save()
    {
        $cols = [];
        $vals = [];
        $db = new DB();
        $db->table(static::$db_tablename);

        foreach(static::$db_columns as $c) {
            if (isset($this->{$c[0]})) {
                $cols[] = $c[0];
                $vals[$c[0]] = $this->{$c[0]};
            }
        }

        $db->save(static::$db_idcolumn, $cols, $vals);
    }

    private function updateTable()
    {
        $db = new DB();
        if (isset(static::$db_tablename)) {
            $db->CreateTable(static::$db_tablename, static::$db_idcolumn);
            if (is_array(static::$db_columns)) {
                $db->SetColumns(static::$db_tablename, static::$db_columns);
            }
        }
    }

}
