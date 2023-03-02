<?php

namespace Zeijaku\Database\Compilers;

use Zeijaku\Database\QueryBuilders\AbstractQueryBuilder;

class BaseCompiler extends AbstractCompiler implements CompilerInterface
{

    /**
     * @param AbstractQueryBuilder $query
     * @return string
     */
    public function compileSelect(AbstractQueryBuilder $query)
    {
        $stmt = "SELECT ";
        $stmt .= $query->getDistinct() ? "DISTINCT " : "";
        $stmt .= implode(', ', $query->getColumns());
        $stmt .= " FROM {$query->getTable()} ";
        $stmt .= $this->compileWheres($query->getWheres());
        $stmt .= !is_null($query->getGroupBy()) ? $this->compileGroupBy($query->getGroupBy()) . " " : "";
        $stmt .= !is_null($query->getOrderBy()) ? $this->compileOrderBy($query->getOrderBy()) . " " : "";
        $stmt .= !is_null($query->getLimit()) ? "LIMIT " . $query->getLimit() . " " : "";
        $stmt .= !is_null($query->getOffset()) ? "OFFSET " . $query->getOffset() : "";

        return trim($stmt).';';
    }

    /**
     * @param AbstractQueryBuilder $query
     * @param array $data
     * @return string
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

        // compile each insert
        foreach ($data as $row) {
            $rows[] = "('".implode('\', \'', array_values($row))."')";
        }

        $stmt .= implode(', ', $rows);

        return trim($stmt).';';
    }

    /**
     * @param AbstractQueryBuilder $query
     * @param array $data
     * @return string
     */
    public function compileUpdate(AbstractQueryBuilder $query, array $data)
    {
        $stmt = "UPDATE {$query->getTable()} SET ";

        foreach ($data as $column => $value) {
            $stmt .= "{$column} = '{$value}', ";
        }

        $stmt = rtrim($stmt, ", ");
        $stmt .= " " . $this->compileWheres($query->getWheres());

        return trim($stmt).';';
    }

    /**
     * @param AbstractQueryBuilder $query
     * @return string
     */
    public function compileDelete(AbstractQueryBuilder $query)
    {
        $stmt = "DELETE FROM {$query->getTable()} ";
        $stmt .= $this->compileWheres($query->getWheres());
        $stmt .= !is_null($query->getGroupBy()) ? $this->compileGroupBy($query->getGroupBy()) : "";
        $stmt .= !is_null($query->getOrderBy()) ? $this->compileOrderBy($query->getOrderBy()) : "";
        $stmt .= !is_null($query->getLimit()) ? "LIMIT " . $query->getLimit() . " " : "";
        $stmt .= !is_null($query->getOffset()) ? "OFFSET " . $query->getOffset() : "";

        return trim($stmt).';';
    }

    /**
     * @param $wheres
     * @return string
     */
    public function compileWheres($wheres)
    {
        $stmt = "";

        if (count($wheres) > 0) {
            $stmt .= "WHERE ";
            list ($column, $operator, $value) = $wheres[0];
            $stmt .= "{$column} {$operator} '{$value}'";

            foreach (array_slice($wheres, 1) as $where) {
                list ($column, $operator, $value, $boolean) = $where;
                $stmt .= " {$boolean} {$column} {$operator} '{$value}'";
            }
        }

        return $stmt;
    }
}