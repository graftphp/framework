<?php

namespace GraftPHP\Framework;

class Model extends DB
{

    static public function all($sortcol = null, $sortdir = null)
    {
        $obj = new static;
        $obj->build();
        $db = new DB();
        $res = $db->table(static::$db_tablename)
            ->get(['id'], $sortcol, $sortdir);

        $out = new Data();
        foreach($res as $row) {
            $out->append( static::find($row->id) );
        }
        return $out;
    }

    static public function build() {
        $obj = new static;
        $obj->updateTable();
        $obj->defaultData();
    }

    public function defaultData()
    {
        if (isset(static::$db_defaultdata)) {
            foreach(static::$db_defaultdata as $k => $d) {
                $obj = new static;
                if (!$obj->find($d[0])) {
                    $obj->{static::$db_idcolumn} = $d[0];
                    foreach(static::$db_columns as $ci => $c) {
                        $obj->{$c[0]} = $d[$ci+1];
                    }
                    $obj->save();
                }
            }
        }
    }

    // delete the current instance of this object
    public function delete_func($id = null)
    {
        $db = new DB();
        $db->table(static::$db_tablename)
            ->where(static::$db_idcolumn, '=', isset($id) ? $id : $this->{static::$db_idcolumn})
            ->delete();
    }

    // delete a single instance of the object by ID
    public function destroy($id)
    {
        $o = static::find($id, $this->{static::$db_idcolumn});
        $o->delete();
    }

    // find and return a single instance of the object
    static public function find($val, $column = null)
    {
        $col = $column ? $column : static::$db_idcolumn;
        $db = new DB();
        $res = $db->table(static::$db_tablename)
            ->where($col, '=', $val)
            ->get();

        if ($res->count() > 0) {
            $obj = new static;
            $obj->{static::$db_idcolumn} = $res->first()->{static::$db_idcolumn};
            foreach(static::$db_columns as $col) {
                $obj->{$col[0]} = $res->first()->{$col[0]};
            }
            return $obj;
        } else {
            return false;
        }
    }

    public function get($cols = null)
    {
        if (empty($this->table)) {
            $this->table = static::$db_tablename;
        }
        return parent::get($cols);
    }

    public function save()
    {
        $cols = [];
        $vals = [];

        foreach(static::$db_columns as $c) {
            if (isset($this->{$c[0]})) {
                $cols[] = $c[0];
                $vals[$c[0]] = $this->{$c[0]};
            }
        }

        if (isset($this->{static::$db_idcolumn})) {
            $db = new DB();
            $db->table(static::$db_tablename)
                ->where(static::$db_idcolumn, '=', $this->{static::$db_idcolumn})
                ->update($cols, $vals);
        } else {
            DB::insert(static::$db_tablename, $cols, $vals);
        }
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
