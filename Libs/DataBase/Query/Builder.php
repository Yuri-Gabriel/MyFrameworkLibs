<?php

namespace Framework\Libs\DataBase\Query;

use Framework\Libs\DataBase\Repository;

abstract class Builder {
    /** @var string $query */
    protected string $query;

    /** @var string $table */
    protected $table;

    /** @var Repository $repository */
    private $repository;
    
    /**
     * @param string $table
     * @param Repository $repository
    */
    public function __construct($table, $repository) {
        $this->query = "";
        $this->table = $table;
        $this->repository = $repository;
    }

    public function getQuery() {
        return $this->query;
    }

    public function run() {
        return $this->repository->run($this->query);
    }
}