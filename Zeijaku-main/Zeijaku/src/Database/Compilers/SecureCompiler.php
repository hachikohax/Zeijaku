<?php

namespace Zeijaku\Database\Compilers;

use Zeijaku\Database\QueryBuilders\AbstractQueryBuilder;

class SecureCompiler extends AbstractCompiler implements CompilerInterface
{

    /**
     * @param AbstractQueryBuilder $query
     * @return mixed
     */
    public function compileSelect(AbstractQueryBuilder $query)
    {
        $stmt = "SELECT ";
        $stmt .= $query->getDistinct() ? "DISTINCT " : "";
        $stmt .= implode(', ', $query->getColumns());
        $stmt .= " FROM {$query->getTable()} ";
        $stmt .= $this->compileWheres($query->getWheres())[0];
        $stmt .= !is_null($query->getGroupBy()) ? $this->compileGroupBy($query->getGroupBy()) . " " : "";
        $stmt .= !is_null($query->getOrderBy()) ? $this->compileOrderBy($query->getOrderBy()) . " " : "";
        $stmt .= !is_null($query->getLimit()) ? " LIMIT " . $query->getLimit() . " " : "";
        $stmt .= !is_null($query->getOffset()) ? " OFFSET " . $query->getOffset() : "";

        $bindings = $this->compileWheres($query->getWheres())[1];
        $stmt = trim($stmt).';';

        var_dump($stmt);
        var_dump($bindings);

        $sql[0] = $stmt;
        $sql[1] = $bindings;

        return $sql;
    }

    /**
     * @param AbstractQueryBuilder $query
     * @param array $data
     * @return mixed
     */
    public function compileInsert(AbstractQueryBuilder $query, array $data)
    {
        $stmt = "INSERT INTO {$query->getTable()}";

        // make data multidimensional array for consistency
        if (count($data) == count($data, COUNT_RECURSIVE)) {
            $data = array($data);
        }

        $keys = array_keys($data[0]);

        // if associative array, set column names on insert
        if (is_string($keys[0])) {
            $stmt .= "(".implode(', ', $keys).") ";
        }

        $stmt .= "VALUES ";

        $rows = [];
        $bindings = [];

        // compile each insert, append a count on each row to differentiate bindings
        $count = 0;
        foreach ($data as $row) {
            $keys = array_map(function($key) use ($count) {
               return ':'.$key.$count;
            }, array_keys($row));

            $rows[] = "(".implode(', ', $keys).")";

            $bindings[] = array_combine($keys, array_values($row));

            $count++;
        }

        $stmt .= implode(', ', $rows);
        $stmt = trim($stmt).';';

        var_dump($stmt);
        var_dump($bindings);

        $sql[0] = $stmt;
        $sql[1] = array_reduce($bindings, 'array_merge', array());

        return $sql;
    }

    /**
     * @param AbstractQueryBuilder $query
     * @param array $data
     * @return mixed
     */
    public function compileUpdate(AbstractQueryBuilder $query, array $data)
    {
        $stmt = "UPDATE {$query->getTable()} SET ";

        $bindings = [];

        foreach ($data as $column => $value) {
            $stmt .= "{$column} = :{$column}, ";
            $bindings[$column] = $value;
        }

        $stmt = rtrim($stmt, ", ");
        $stmt .= " " . $this->compileWheres($query->getWheres())[0];

        $whereBindings = array_keys($this->compileWheres($query->getWheres())[1]);
        $whereBindings = array_combine($whereBindings, $this->compileWheres($query->getWheres())[1]);
        $bindings = array_merge($bindings, $whereBindings);


        $stmt = trim($stmt).';';

        var_dump($stmt);
        var_dump($bindings);

        $sql[0] = $stmt;
        $sql[1] = $bindings;

        return $sql;
    }

    /**
     * @param AbstractQueryBuilder $query
     * @return mixed
     */
    public function compileDelete(AbstractQueryBuilder $query)
    {
        $stmt = "DELETE FROM {$query->getTable()} ";
        $stmt .= $this->compileWheres($query->getWheres())[0];
        $stmt .= !is_null($query->getGroupBy()) ? $this->compileGroupBy($query->getGroupBy()) : "";
        $stmt .= !is_null($query->getOrderBy()) ? $this->compileOrderBy($query->getOrderBy()) : "";
        $stmt .= !is_null($query->getLimit()) ? "LIMIT " . $query->getLimit() . " " : "";
        $stmt .= !is_null($query->getOffset()) ? "OFFSET " . $query->getOffset() : "";

        $bindings = $this->compileWheres($query->getWheres())[1];
        $stmt = trim($stmt).';';

        var_dump($stmt);
        var_dump($bindings);

        $sql[0] = $stmt;
        $sql[1] = $bindings;

        return $sql;
    }

    /**
     * @param $wheres
     * @return mixed
     */
    public function compileWheres($wheres)
    {
        $stmt = "";
        $bindings = [];

        if (count($wheres) > 0) {
            $stmt .= "WHERE ";
            list ($column, $operator, $value) = $wheres[0];
            // append W to each binding to prevent them from overriding other bindings
            $stmt .= "{$column} {$operator} :{$column}WBIND";
            $bindings[$column."WBIND"] = $value;

            foreach (array_slice($wheres, 1) as $where) {
                list ($column, $operator, $value, $boolean) = $where;
                $stmt .= " {$boolean} {$column} {$operator} :{$column}";
                $bindings[$column] = $value;
            }
        }

        $sql[0] = $stmt;
        $sql[1] = $bindings;

        return $sql;
    }
}