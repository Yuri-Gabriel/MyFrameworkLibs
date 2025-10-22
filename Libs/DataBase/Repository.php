<?php
/**
 * Exemplo de implementação da classe Repository
 * para demonstrar a leitura dos metadados do modelo
 * via Reflection e a construção de queries básicas.
 *
 * NOTA: Esta implementação assume um objeto de conexão $db (mock)
 * e usa Reflection para ler os atributos (annotations).
 */

namespace Framework\Libs\DataBase;

// Importar os atributos necessários
use Framework\Libs\Annotations\DataBase\Collumn;
use Framework\Libs\Annotations\DataBase\Model;
use Framework\Libs\Annotations\DataBase\PrimaryKey;
use Framework\Libs\Annotations\DataBase\ForeignKey; // Embora não usado nas funções CRUD básicas, é bom ter

// ----------------------------------------------------------------------
// Mock de Dependências (Substitua por suas classes reais)
// ----------------------------------------------------------------------

// Mock para a classe de conexão, pois o ORM precisará de uma conexão.
class DatabaseConnectionMock {
    public function query(string $sql): array {
        echo "EXECUTING SQL (MOCK): {$sql}\n";
        // Simula dados retornados pelo DB
        return [['id' => 1, 'nome' => 'Dado Mock', 'id_endereco' => 10]];
    }

    public function execute(string $sql, array $params = []): void {
        echo "EXECUTING SQL (MOCK): {$sql} with params: " . json_encode($params) . "\n";
        // Simula um INSERT/UPDATE
    }
}

// ----------------------------------------------------------------------
// Classe Repository
// ----------------------------------------------------------------------

/**
 * @template T of object
 */
class Repository
{
    /** @var string O nome da tabela extraído do atributo #[Model]. */
    protected string $table = "";

    /** @var string O FQCN da classe do modelo (ex: App\Model\Pessoa). */
    protected string $modelClass;

    /** @var string O nome da coluna da chave primária (ex: 'id'). */
    protected string $primaryKeyColumn = 'id';

    /** @var string O nome da propriedade da chave primária no modelo (ex: '$id'). */
    protected string $primaryKeyProperty = 'id';

    /** @var DatabaseConnectionMock O objeto de conexão com o banco de dados. */
    private DatabaseConnectionMock $db;

    /**
     * @param string $modelClass O FQCN (Fully Qualified Class Name) do modelo (T).
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->db = new DatabaseConnectionMock(); // Inicia o mock da conexão
        $this->initializeMetadata();
    }

    /**
     * Usa Reflection para ler os atributos #[Model] e #[PrimaryKey].
     */
    private function initializeMetadata(): void
    {
        try {
            $reflection = new \ReflectionClass($this->modelClass);

            // 1. Ler o nome da tabela a partir de #[Model]
            $modelAttributes = $reflection->getAttributes(Model::class);
            if (empty($modelAttributes)) {
                throw new \RuntimeException("Model class '{$this->modelClass}' requires the #[Model] attribute.");
            }
            // Assume que o construtor de Model recebe o nome da tabela
            $this->table = $modelAttributes[0]->newInstance()->name;

            // 2. Encontrar a chave primária
            foreach ($reflection->getProperties() as $property) {
                $pkAttributes = $property->getAttributes(PrimaryKey::class);
                if (!empty($pkAttributes)) {
                    $this->primaryKeyProperty = $property->getName();

                    // Tentativa de obter o nome da coluna a partir de #[Collumn]
                    $columnAttributes = $property->getAttributes(Collumn::class);
                    if (!empty($columnAttributes)) {
                        $this->primaryKeyColumn = $columnAttributes[0]->newInstance()->name;
                    } else {
                        // Caso #[Collumn] não esteja presente, usa o nome da propriedade
                        $this->primaryKeyColumn = $this->primaryKeyProperty;
                    }
                    return; // Chave primária encontrada
                }
            }

        } catch (\ReflectionException $e) {
            throw new \RuntimeException("Failed to reflect model class: " . $e->getMessage());
        }
    }

    /**
     * Converte um array de dados do DB para uma instância do Modelo.
     * @param array $data O array de dados da linha do banco de dados.
     * @return T
     */
    private function hydrate(array $data): object
    {
        $model = new ($this->modelClass)();
        $reflection = new \ReflectionClass($this->modelClass);

        foreach ($reflection->getProperties() as $property) {
            $propName = $property->getName();
            $columnAttributes = $property->getAttributes(Collumn::class);
            $columnName = !empty($columnAttributes)
                ? $columnAttributes[0]->newInstance()->name
                : $propName;

            if (isset($data[$columnName])) {
                // Set the property value, ensuring accessibility
                $property->setValue($model, $data[$columnName]);
            }
        }
        return $model;
    }

    // ----------------------------------------------------------------------
    // CRUD Métodos
    // ----------------------------------------------------------------------

    /**
     * Busca um registro pelo valor da chave primária.
     * @param int|string $id
     * @return T|null
     */
    public function find(int $id): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKeyColumn} = ?";
        $result = $this->db->query($sql);

        if (!empty($result)) {
            echo "Successfully found '{$this->modelClass}' by ID: {$id}\n";
            return $this->hydrate($result[0]);
        }

        echo "No '{$this->modelClass}' found with ID: {$id}\n";
        return null;
    }

    /**
     * Busca todos os registros.
     * @return array<T>
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $results = $this->db->query($sql);
        $entities = [];

        foreach ($results as $data) {
            $entities[] = $this->hydrate($data);
        }

        echo "Successfully found " . count($entities) . " entities for '{$this->modelClass}'\n";
        return $entities;
    }

    /**
     * Salva (Insere ou Atualiza) o modelo.
     *
     * @param T $entity A instância do modelo a ser salva.
     * @throws \RuntimeException Se a entidade não for do tipo esperado.
     */
    public function save(object $entity): void
    {
        if (!$entity instanceof $this->modelClass) {
            throw new \RuntimeException("Entity must be an instance of '{$this->modelClass}'.");
        }

        $reflection = new \ReflectionClass($entity);
        $propertiesToSave = [];
        $columns = [];
        $values = [];

        // 1. Coletar dados de todas as propriedades com #[Collumn]
        foreach ($reflection->getProperties() as $property) {
            $columnAttributes = $property->getAttributes(Collumn::class);
            if (empty($columnAttributes)) {
                continue;
            }

            $columnName = $columnAttributes[0]->newInstance()->name;
            $propertyValue = $property->getValue($entity);

            if ($property->getName() === $this->primaryKeyProperty) {
                $primaryKeyValue = $propertyValue;
                continue; // PK é tratada separadamente para INSERT/UPDATE
            }

            $columns[] = $columnName;
            $values[] = $propertyValue;
            $propertiesToSave[$columnName] = $propertyValue;
        }


        // 2. Determinar se é INSERT ou UPDATE
        // Assumimos que se a PK for definida (ex: > 0), é um UPDATE
        if (isset($primaryKeyValue) && $primaryKeyValue > 0) {
            // UPDATE: SET column1 = ?, column2 = ? WHERE pk = ?
            $setClauses = array_map(fn($col) => "{$col} = ?", $columns);
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses)
                . " WHERE {$this->primaryKeyColumn} = ?";

            $params = array_values($propertiesToSave);
            $params[] = $primaryKeyValue;

            $this->db->execute($sql, $params);
            echo "Updated existing '{$this->modelClass}' with ID: {$primaryKeyValue}\n";

        } else {
            // INSERT: (column1, column2) VALUES (?, ?)
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $columnsSql = implode(', ', $columns);
            $sql = "INSERT INTO {$this->table} ({$columnsSql}) VALUES ({$placeholders})";

            $this->db->execute($sql, $values);
            echo "Inserted new '{$this->modelClass}'\n";
            // Em um ORM real, você obteria o ID inserido aqui.
        }
    }
}

// ----------------------------------------------------------------------
// Exemplo de Uso
// ----------------------------------------------------------------------

// NOTA: Para este exemplo rodar, você precisaria importar as classes de modelo.
// Usaremos as classes definidas em 'orm_test_models.php' assumindo que estão no escopo.

// Exemplo de como você usaria no seu código:
/*
$pessoaRepository = new Repository(\App\Model\Pessoa::class);

// Teste FIND
$pessoa = $pessoaRepository->find(1);

// Teste SAVE (UPDATE)
if ($pessoa) {
    $pessoa->nome = "Novo Nome Teste";
    $pessoaRepository->save($pessoa);
}

// Teste SAVE (INSERT - Criando uma nova Pessoa)
$novaPessoa = new \App\Model\Pessoa();
$novaPessoa->id = 0; // Indica que é um novo registro
$novaPessoa->nome = "Alice do Nascimento";
$novaPessoa->id_endereco = 15;
$pessoaRepository->save($novaPessoa);
*/
