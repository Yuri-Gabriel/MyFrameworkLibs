<?php

namespace Framework\Kernel\Model;

use Framework\Kernel\ClassLoader;
use Framework\Kernel\Kernable;

use Framework\Libs\Annotations\DataBase\Model;
use Framework\Libs\Annotations\DataBase\Collumn;
use Framework\Libs\Annotations\DataBase\ForeignKey;
use Framework\Libs\Annotations\DataBase\PrimaryKey;
use Framework\Libs\Exception\ModelException;
use ReflectionClass;
use ReflectionProperty;

class ModelKernel implements Kernable {

    private array $types;
    public function __construct() {
        $pathModel = $_SERVER["DOCUMENT_ROOT"] . "/App/Model";
        ClassLoader::load($pathModel);

        $modelClasses = ClassLoader::getClasses($pathModel);

        $this->types = [
            "int" => "INTEGER",
            "string" => "VARCHAR(255)",
            "bool" => "BOOLEAN",
            "float" => "FLOAT"
        ];

        $tables = $this->interpret($modelClasses);
        $this->sortTables($tables);
        $this->buildSQLs($tables); 
    }

    public function run(): void {
        
    } 

    private function interpret(array $modelClasses) {
        $tables = [];
        foreach ($modelClasses as $className) {
            $class = new ReflectionClass($className);
            $table = "";
            if($this->isModel($class, $table)) {
                $entity = new Entity($table, []);
                $collumns = [];
                $props = $class->getProperties();
                foreach($props as $prop) {
                    $collumn_name = "";
                    if(!$this->isCollumn($prop, $collumn_name)) continue;
                    $fk = null;
                    $other_table = "";
                    $other_table_id = "";
                    $delete_cascade = false;
                    if($this->isForeignKey($prop, $other_table, $other_table_id, $delete_cascade)) {
                        $other_table_name = "";

                        if(!$this->isModel(
                            new ReflectionClass($other_table), 
                            $other_table_name
                        )) throw new ModelException(
                            "The class $other_table don't is a model"
                        );

                        $fk = new TableForeignKey(
                            $other_table_name,
                            $collumn_name,
                            $other_table_id,
                            $delete_cascade
                        );
                    }
                    $pk = null;
                    $autoincrement = false;
                    if($this->isPrimaryKey($prop, $autoincrement)) {
                        $pk = new TablePrimaryKey($autoincrement);
                    }

                    $collumn = new TableCollumn(
                        $collumn_name,
                        $this->types[$prop->getType()->getName()],
                        $pk,
                        $fk
                    );

                    $collumns[] = $collumn;

                }
                $entity->collumns = $collumns;
                $tables[] = $entity;
            }
        }
        
        return $tables;
    }

    private function sortTables(array &$tables) {
        for($i = 0; $i < count($tables); $i++) {
            $table_fk = "";
            foreach($tables[$i]->collumns as $i_collumn) {
                if(!empty($i_collumn->fk)) {
                    $table_fk = $i_collumn->fk->other_table;
                    break;
                }
            }
            if($table_fk == "") continue;

            for($j = 0; $j < count($tables); $j++) {
                if($table_fk == $tables[$j]->table && $i < $j) {
                    $temp = $tables[$i];
                    $tables[$i] = $tables[$j];
                    $tables[$j] = $temp;
                    $i = 0;
                    break;
                }
            }
        }
    }

    private function buildSQLs(array $tables) {
        $sql = "";
        foreach($tables as $table) {
            $sql .= "\nCREATE TABLE IF NOT EXISTS $table->table (";
            foreach($table->collumns as $collumn) {
                $name = $collumn->name;
                $type = $collumn->type;
                $pk = "";
                if($collumn->pk) {
                    $autoincrement = $collumn->pk->autoincrement ? "AUTO_INCREMENT" : "";
                    $pk = "$autoincrement PRIMARY KEY";
                }
                $sql .= "\n\t$name $type $pk";
                
                if($collumn->fk) {
                    $other_table_name = $collumn->fk->other_table;
                    $from_collumn = $collumn->fk->from_collumn;
                    $to_collumn = $collumn->fk->to_collumn;
                    $delete_cascade = $collumn->fk->delete_cascade ? "ON DELETE CASCADE" : "";
                    $sql .= "\n\tFOREIGN KEY ($from_collumn) REFERENCES $other_table_name($to_collumn) $delete_cascade";
                }
            }
            $sql .= "\n)";
        }
        
        echo "<pre>";
        echo $sql;
        die;
    }

    private function isForeignKey(ReflectionProperty $prop, string &$other_table, string &$other_table_id, bool &$delete_cascade): bool {
        $prop_atributes = $prop->getAttributes(ForeignKey::class);
        foreach($prop_atributes as $attr) {
            if($attr->getName() == ForeignKey::class) {
                $instance = $attr->newInstance();
                $other_table = $instance->table;
                $other_table_id = $instance->fk_column;
                $delete_cascade = $instance->delete_cascade;
                return true;
            }
        }
        return false;
    }

    private function isPrimaryKey(ReflectionProperty $prop, bool &$autoincrement): bool {
        $prop_atributes = $prop->getAttributes(PrimaryKey::class);
        foreach($prop_atributes as $attr) {
            if($attr->getName() == PrimaryKey::class) {
                $instance = $attr->newInstance();
                $autoincrement = $instance->autoincrement;
                return true;
            }
        }
        return false;
    }

    private function isCollumn(ReflectionProperty $prop, string &$name = ""): bool {
        $prop_atributes = $prop->getAttributes(Collumn::class);
        foreach($prop_atributes as $attr) {
            if($attr->getName() == Collumn::class) {
                $name = $attr->newInstance()->name;
                return true;
            }
        }
        return false;
    }

    private function isModel(ReflectionClass $class, string &$table = ""): bool {
        $class_atributes = $class->getAttributes(Model::class);
        foreach($class_atributes as $attr) {
            if($attr->getName() == Model::class) {
                $table = $attr->newInstance()->table;
                return true;
            }
        }
        return false;
    }
}