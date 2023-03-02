<?php

namespace Zeijaku\Database\QueryBuilders;

use PDO;

class BaseQueryBuilder extends AbstractQueryBuilder
{

    /**
     * @param $stmt
     * @return array
     */
    public function execute($stmt)
    {
        return $this->connection->query($stmt, PDO::FETCH_ASSOC)->fetchAll();
    }
}