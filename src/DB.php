<?php

namespace GraftPHP\Framework;

class DB
{

    private $cols = '*';
    private $params = [];
    private $sql = '';
    private $table = '';
    private $where = ' WHERE 1 ';

    public function __construct()
    {
        // connect to db
        try {
            $this->db = new \PDO("mysql:host=" . GRAFT_CONFIG['DBHost'] . ";dbname=" . GRAFT_CONFIG['DBName'],
                GRAFT_CONFIG['DBUser'],
                GRAFT_CONFIG['DBPassword']);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    private function columnExists($tablename, $column)
    {
        $this->query = $this->db->prepare("SHOW COLUMNS FROM {$tablename} LIKE :column");
        $this->query->execute(array("column" => $column));
        return $this->query->fetch() ? true : false;
    }

    public function createTable($tablename, $idcolumn)
    {
        $this->execute("CREATE TABLE IF NOT EXISTS {$tablename} (
            {$idcolumn} INT NOT NULL AUTO_INCREMENT PRIMARY KEY);");
    }

    public static function delete($table, $column, $val)
    {
        $db = new static;
        $db->execute("DELETE FROM " . $table . " WHERE " . $column . " = " . $val);
    }

    /*
    Execute a raw SQL query + optional named parameter array
    */
    public function execute($sql, $params = false)
    {
        $this->sql = $sql;
        if ($params) {
            $this->params = $params;
        }

        $this->run();
        if (strstr($this->sql, 'INSERT')) {
            $this->InsertID = $this->db->lastInsertId();
        }
    }

    public function first($cols = null, $sortcol = null, $sortdir = null)
    {
        $first = $this->get($cols, $sortcol, $sortdir);

        if ($first) {
            return $first[0];
        } else {
            return false;
        }
    }

    public function get($cols = null, $sortcol = null, $sortdir = null)
    {
        if (isset($cols)) {
            if (is_array($cols)) {
                $this->cols = "`" . implode("`,`", $cols) . "`";
            } elseif ($cols != '*') {
                dd('Columns should be an array');
            }
        }
        $this->sql = "SELECT " . $this->cols . "
            FROM `" . $this->table . "`" . $this->where;

        $this->run();

        if ($this->query->rowCount() > 0) {
            $data = $this->query->fetchAll(\PDO::FETCH_ASSOC);
            if ($sortcol && $sortdir) {
                uasort($data, function($a, $b) use ($sortcol,$sortdir) {
                    switch (strtoupper($sortdir)) {
                        case "DESC": return $a[$sortcol] < $b[$sortcol];
                        case "ASC" : return $a[$sortcol] > $b[$sortcol];
                    }
                });
            }
            return $data;
        } else {
            return false;
        }
    }

    public function save($idcol, $cols, $vals)
    {
        $sql = '';
        if (array_search($idcol, $cols)) {
            // id column present, perform update
            $sql = 'REPLACE';
        } else {
            // no id column, perform insert
            $sql = 'INSERT';
        }
        $sql .= ' INTO `' . $this->table . '` (`';
        $sql .= implode('`,`', $cols);
        $sql .= '`) VALUES (:';
        $sql .= implode(',:', $cols);
        $sql .= ');';
        
        $this->execute($sql, $vals);
    }

    private function run()
    {
        $this->query = $this->db->prepare($this->sql);
        $this->query->execute($this->params);
    }

    public function setColumns($tablename, $columns)
    {
        // TODO: remove columns not in the model settings
        foreach ($columns as $column) {
            if ($this->ColumnExists($tablename, $column[0])) {
                $this->execute("ALTER TABLE {$tablename} CHANGE {$column[0]} {$column[0]} {$column[1]}");
            } else {
                $this->execute("ALTER TABLE {$tablename} ADD {$column[0]} {$column[1]}");
            }
        }
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function where($column, $operator, $value)
    {
        // TODO: validate operators
        $this->where .= " AND `$column` $operator :p" . count($this->params);
        $this->params['p'.count($this->params)] = $value;

        return $this;        
    }

}
