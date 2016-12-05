<?php

namespace GraftPHP\Framework;

class DB
{

    use MagicCall;

    private $cols = '*';
    private $orderSQL = '';
    private $params = [];
    private $sql = '';
    private $where = ' WHERE 1 ';

    protected $table = '';

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

    public function count()
    {
        return count((array)$count);
    }

    public function createTable($tablename, $idcolumn)
    {
        $this->execute("CREATE TABLE IF NOT EXISTS {$tablename} (
            {$idcolumn} INT NOT NULL AUTO_INCREMENT PRIMARY KEY);");
    }

    /*
    Delete records based on table and query settings
    */
    public function delete_func()
    {
        if (empty($this->table) || empty($this->where)) {
            dd('Table and Where required for delete');
        }
        $this->sql = 'DELETE FROM `' . $this->table . '`' . $this->where;
        $this->run();
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

    public function get($cols = null)
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
            $data = $this->query->fetchAll(\PDO::FETCH_OBJ);
            return Data::populate((object)$data);
        } else {
            return new Data;
        }
    }

    static public function insert($table, $cols, $vals)
    {
        $sql = 'INSERT INTO `' . $table . '`(`';
        $sql .= implode('`,`', $cols);
        $sql .= '`) VALUES (:';
        $sql .= implode(',:', $cols);
        $sql .= ');';

        $self = new static;
        $self->sql = $sql;
        $self->params = $vals;
        $self->run();

        return $self->db->lastInsertId();
    }

    public function orderBy_func($sortcol, $sortdir = null)
    {
        if (empty($this->orderSQL)) {
            $this->orderSQL = ' ORDER BY `' . $sortcol . '` ';
        } else {
            $this->orderSQL .= ', `' . $sortcol . '` ';
        }
        $this->orderSQL .= $sortdir == 'DESC' ? 'DESC' : 'ASC';

        return $this;
    }

    static public function replace($table, $cols, $vals)
    {
        $sql = 'REPLACE INTO `' . $table . '`(`';
        $sql .= implode('`,`', $cols);
        $sql .= '`) VALUES (:';
        $sql .= implode(',:', $cols);
        $sql .= ');';

        $db = new static;
        $db->sql = $sql;
        $db->params = $vals;
        $db->run();
    }

    private function run()
    {
        $this->query = $this->db->prepare($this->sql . $this->orderSQL);
        $this->query->execute($this->params);
    }

    public function setColumns($tablename, $columns)
    {
        // TODO: remove columns not in the model settings
        foreach ($columns as $column) {
            if ($this->ColumnExists($tablename, $column[0])) {
                $this->execute("ALTER TABLE `{$tablename}` CHANGE `{$column[0]}` `{$column[0]}` {$column[1]}");
            } else {
                $this->execute("ALTER TABLE `{$tablename}` ADD `{$column[0]}` {$column[1]}");
            }
        }
    }

    public function table_func($table)
    {
        $this->table = $table;

        return $this;
    }

    public function update($cols, $vals)
    {
        $sql = '';
        foreach($cols as $c) {
            $sql .= strlen($sql) > 0 ? ', ' : '';
            $sql .= '`' . $c . '` = :' . $c . ' ';
        }
        $sql = 'UPDATE `' . $this->table . '` SET ' . $sql . $this->where;

        $this->sql = $sql;
        $this->params = array_merge($this->params, $vals);
        $this->run();
    }

    public function where_func($column, $operator, $value)
    {
        $this->where .= " AND `$column` $operator :p" . count($this->params);
        $this->params['p'.count($this->params)] = $value;

        return $this;
    }

}
