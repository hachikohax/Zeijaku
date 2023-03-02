<?php

namespace Zeijaku\Database\QueryBuilders;

use PDO;

class SecureQueryBuilder extends AbstractQueryBuilder
{
    /**
     * @param $query
     * @return array
     */
    public function execute($query)
    {
        if (!is_array($query)) {
            $query = array($query);
            $query[1] = null;
        }

        $stmt = $query[0];
        $bindings = $query[1];

        $handle = $this->connection->prepare($stmt);
        $handle->execute($bindings);
        return $handle->fetchAll(PDO::FETCH_ASSOC);
    }
}