<?php

namespace Zeijaku\Database\Compilers;

use Zeijaku\Database\QueryBuilders\AbstractQueryBuilder;

abstract class AbstractCompiler implements CompilerInterface
{

    /**
     * @param $groupBy
     * @return string
     */
    public function compileGroupBy($groupBy)
    {
        $stmt = "GROUP BY ";
        $stmt .= implode(', ', $groupBy);
        return $stmt;
    }

    /**
     * @param $orderBy
     * @return string
     */
    public function compileOrderBy($orderBy)
    {
        $stmt = "ORDER BY ";

        foreach ($orderBy as $column => $direction)
        {
            $direction = strtoupper($direction);
            $stmt .= "{$column} {$direction}, ";
        }

        return rtrim($stmt, ', ');
    }

    /**
     * @param AbstractQueryBuilder $query
     * @return mixed
     */
    abstract function compileSelect(AbstractQueryBuilder $query);

    /**
     * @param AbstractQueryBuilder $query
     * @param array $data
     * @return mixed
     */
    abstract function compileInsert(AbstractQueryBuilder $query, array $data);

    /**
     * @param AbstractQueryBuilder $query
     * @param array $data
     * @return mixed
     */
    abstract function compileUpdate(AbstractQueryBuilder $query, array $data);

    /**
     * @param AbstractQueryBuilder $query
     * @return mixed
     */
    abstract function compileDelete(AbstractQueryBuilder $query);

    /**
     * @param $wheres
     * @return mixed
     */
    abstract function compileWheres($wheres);
}