<?php

namespace Framework\Libs\DataBase;

use Framework\Kernel\EnvLoad;
use Exception;
use PDO;
use PDOException;

class Conection {

    /** @var PDO $pdo */
    private $pdo;

    public function __construct() {

        EnvLoad::load();
        
        try {
            $this->pdo = new PDO(
                "{$_ENV["DB_CONNECTION"]}:host={$_ENV["DB_HOST"]};port={$_ENV["DB_PORT"]};dbname={$_ENV["DB_DATABASE"]}",
                $_ENV["DB_USERNAME"],
                $_ENV["DB_PASSWORD"],
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return;
        } catch (PDOException $errPDO) {
            echo $errPDO->getMessage();
        } catch (Exception $err) {
            echo $err->getMessage();
        }
        die;
    }

    /**
     * @param string $query
     * @return array
    */
    public function run($query) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $errPDO) {
            echo $errPDO->getMessage();
        } catch (Exception $err) {
            echo $err->getMessage();
        }
        die;  
    }
       

    /**
     * @return void
    */
    public function startTransaction() {
        $this->pdo->beginTransaction();
    }

    /**
     * @return void
    */
    public function commit() {
        $this->pdo->commit();
    }

    /**
     * @return void
    */
    public function rollback() {
        $this->pdo->rollBack();
    }
}