<?php

namespace GraftPHP\Framework;

class Model
{

    public function __construct()
    {
        $this->updateTable();
    }

    static public function all($sortcol = null, $sortdir = null)
    {
        $db = new DB();
        return $db->table(static::$db_tablename)
            ->get('*', $sortcol, $sortdir);
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

    static public function find($id)
    {
        $db = new DB();
        $res = $db->table(static::$db_tablename)
            ->where(static::$db_idcolumn, '=', $id)
            ->get();
        $obj = new static;
        $obj->{static::$db_idcolumn} = $res[static::$db_idcolumn];
        foreach(static::$db_columns as $col) {
            $obj->{$col[0]} = $res[$col[0]];
        }
        return $obj;
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
