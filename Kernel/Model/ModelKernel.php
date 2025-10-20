<?php

namespace Framework\Kernel\Model;

use Framework\Kernel\ClassLoader;
use Framework\Kernel\Kernable;

use Framework\Libs\Annotations\DataBase\Model;
use Framework\Libs\Annotations\DataBase\Collumn;
use Framework\Libs\Annotations\DataBase\ForeignKey;
use Framework\Libs\Annotations\DataBase\PrimaryKey;

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
        $this->interpret($modelClasses);   
    }

    public function run(): void {
        
    } 

    private function interpret(array $modelClasses) {
        $tables = [];
        echo "<pre>";
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
                    if($this->isForeignKey($prop, $other_table, $other_table_id)) {
                        $fk = new TableForeignKey(
                            $other_table,
                            $collumn_name,
                            $other_table_id
                        );
                    }
                    $collumn = new TableCollumn(
                        $collumn_name,
                        $this->types[$prop->getType()->getName()],
                        $fk
                    );

                    $collumns[] = $collumn;

                }
                $entity->collunmns = $collumns;
                $tables[] = $entity;
            }
        }
        print_r($tables);
        die;
    }

    private function isForeignKey(ReflectionProperty $prop, string &$other_table = "", string &$other_table_id = ""): bool {
        $prop_atributes = $prop->getAttributes(ForeignKey::class);
        foreach($prop_atributes as $attr) {
            if($attr->getName() == ForeignKey::class) {
                $instance = $attr->newInstance();
                $other_table = $instance->table;
                $other_table_id = $instance->fk_column;
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