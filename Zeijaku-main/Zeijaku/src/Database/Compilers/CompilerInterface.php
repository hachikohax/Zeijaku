<?php

namespace Zeijaku\Database\Compilers;

use Zeijaku\Database\QueryBuilders\AbstractQueryBuilder;

interface CompilerInterface
{

    /**
     * @param AbstractQueryBuilder $query
     * @return mixed
     */
    public function compileSelect(AbstractQueryBuilder $query);

    /**
     * @param AbstractQueryBuilder $query
     * @param array $data
     * @return mixed
     */
    public function compileInsert(AbstractQueryBuilder $query, array $data);

    /**
     * @param AbstractQueryBuilder $query
     * @param array $data
     * @return mixed
     */
    public function compileUpdate(AbstractQueryBuilder $query, array $data);

    /**
     * @param AbstractQueryBuilder $query
     * @return mixed
     */
    public function compileDelete(AbstractQueryBuilder $query);
}