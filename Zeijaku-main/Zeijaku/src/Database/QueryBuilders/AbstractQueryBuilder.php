<?php

namespace Zeijaku\Database\QueryBuilders;

use PDO;
use Zeijaku\Database\Compilers\CompilerInterface;

abstract class AbstractQueryBuilder
{
    protected $compiler;
    protected $connection;

    protected $table;
    protected $wheres;
    protected $columns;
    protected $distinct = false;
    protected $orderBy;
    protected $groupBy;
    protected $limit;
    protected $offset;

    protected $operators = array(
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'LIKE', 'NOT LIKE', 'BETWEEN', 'ILIKE',
        '&', '|', '^', '<<', '>>',
        'RLIKE', 'REGEXP', 'NOT REGEXP',
        '~', '~*', '!~', '!~*'
    );

    /**
     * @param PDO $connection
     * @param CompilerInterface $compiler
     */
    public function __construct(PDO $connection, CompilerInterface $compiler)
    {
        $this->connection = $connection;
        $this->compiler = $compiler;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return mixed
     */
    public function getWheres()
    {
        return $this->wheres;
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return bool
     */
    public function getDistinct()
    {
        return $this->distinct;
    }

    /**
     * @return mixed
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @return mixed
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function select($columns = array("*"))
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * @param $column
     * @param string $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = "=", $value = null, $boolean = "AND")
    {
        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->where($key, $value);
            }

            return $this;
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = "=";
        }

        $operator = strtoupper($operator);

        if (!in_array($operator, $this->operators)) {
            throw new \InvalidArgumentException("Invalid Operator: [{$operator}]");
        }

        $this->wheres[] = array($column, $operator, $value, $boolean);

        return $this;
    }

    /**
     * @param $column
     * @param string $operator
     * @param null $value
     * @return $this
     */
    public function orWhere($column, $operator = "=", $value = null)
    {
        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->where($key, "=", $value, "OR");
            }

            return $this;
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = "=";
        }

        $this->where($column, $operator, $value, "OR");

        return $this;
    }

    /**
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function limit($value)
    {
        $this->limit = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function offset($value)
    {
        $this->offset = $value;
        return $this;
    }

    /**
     * @param $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = "ASC")
    {
        $this->orderBy = [];

        if (is_array($column)) {
            $this->orderBy = $column;
        } else {
            $this->orderBy[$column] = $direction;
        }

        return $this;
    }

    /**
     * @param $column
     * @return $this
     */
    public function groupBy($column)
    {
        $this->groupBy = func_get_args();
        return $this;
    }

    /**
     * @param null $columns
     * @return mixed
     */
    public function get($columns = null)
    {
        if (is_null($this->columns)) {
            func_get_args() ? $this->select(func_get_args()) : $this->select("*");
        }

        $stmt = $this->compiler->compileSelect($this);
        return $this->execute($stmt);
    }

    /**
     * @param $data
     */
    public function insert($data)
    {
        if (func_num_args() > 1) {
            $data = func_get_args();
        }

        $stmt = $this->compiler->compileInsert($this, $data);
        $this->execute($stmt);
    }

    /**
     * @param $data
     */
    public function update($data)
    {
        $stmt = $this->compiler->compileUpdate($this, $data);
        $this->execute($stmt);
    }

    /**
     *
     */
    public function delete()
    {
        $stmt = $this->compiler->compileDelete($this);
        $this->execute($stmt);
    }

    /**
     *
     */
    public function truncate()
    {
        $table = $this->getTable();

        $stmt = "TRUNCATE TABLE {$table};";
        $this->execute($stmt);
    }

    /**
     * @param $stmt
     * @return mixed
     */
    abstract function execute($stmt);
}