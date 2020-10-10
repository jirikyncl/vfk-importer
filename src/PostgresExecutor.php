<?php

namespace VfkImporter;

/**
 * Class PostgresExecutor
 *
 * @author Jiri Kyncl
 */
class PostgresExecutor implements IExecutor
{
    const CONNECTION_STRING = "host=%s port=%s dbname=%s user=%s password=%s";
    const CREATE_TABLE_STRING = "CREATE TABLE IF NOT EXISTS %s ();";
    const ADD_COLUMN_STRING = "ALTER TABLE IF EXISTS %s ADD COLUMN IF NOT EXISTS %s %s;";
    const TRUNCATE_TABLE_STRIING = "TRUNCATE TABLE %s;";
    const INSERT_DATA_STRING = "INSERT INTO %s VALUES(%s);";
    const INSERT_PAGE_SIZE = 1;

    private $connection;
    private $schema = 'public';
    private $createTables = true;
    private $truncateTables = false;

    /**
     * PostgresExecutor constructor.
     * @param DbConfig $dbConfig
     */
    public function __construct(DbConfig $dbConfig)
    {
        $this->connection = pg_connect(sprintf(
            self::CONNECTION_STRING,
            $dbConfig->host,
            $dbConfig->port,
            $dbConfig->database,
            $dbConfig->username,
            $dbConfig->password
        ));
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        pg_close($this->connection);
    }

    /**
     * Execute
     * @param Vfk $vfk
     */
    public function execute(Vfk $vfk): void
    {
        $this->checkBeforeExecute($vfk);

        $beginQueries = ["SET LOCAL search_path TO $this->schema;"];
        $blockQueries = $this->createBlockQueries($vfk);
        $dataQueries = $this->createDataQueries($vfk);
        $initQueries = array_merge($beginQueries, $blockQueries);
        $initQueryString = implode(PHP_EOL, $initQueries);

        pg_query($this->connection, "BEGIN");
        $success = @pg_query($this->connection, $initQueryString);

        // Paging over data inserts
        $chunkedDataQueries = array_chunk($dataQueries, self::INSERT_PAGE_SIZE);
        foreach ($chunkedDataQueries as $chunk) {
            if (!$success) {
                break;
            }
            $dataQueryString = implode(PHP_EOL, $chunk);
            $success = pg_query($this->connection, $dataQueryString);
        }

        if ($success) {
            pg_query($this->connection, "COMMIT");
        } else {
            $err = pg_last_error($this->connection);
            pg_query($this->connection, "ROLLBACK");
            throw new ExecuteException($err);
        }
    }

    /**
     * Check VFK content
     * @param Vfk $vfk
     */
    private function checkBeforeExecute(Vfk $vfk)
    {
        if (empty($vfk->dataRows) && empty($vfk->blockRows)) {
            throw new ExecuteException("Nothing to execute");
        }
    }

    /**
     * Create queries for block definitions
     * @param Vfk $vfk
     * @return string[]
     */
    private function createBlockQueries(Vfk $vfk): array
    {
        $queries = [];

        foreach ($vfk->blockRows as $block) {

            if ($this->createTables) {
                $queries[] = sprintf(self::CREATE_TABLE_STRING, $block->getTable());

                foreach ($block->getContent() as $columnDefinition) {
                    $length = $columnDefinition->length;
                    $type = "timestamp";

                    if ($columnDefinition->type === ColumnDefinition::TYPE_NUMBER) {
                        $precisionParts = explode(".", $length);
                        $precision = count($precisionParts) > 1
                            ? $precisionParts[0] . "," . $precisionParts[1]
                            : $precisionParts[0];
                        $type = "numeric($precision)";
                    } elseif ($columnDefinition->type === ColumnDefinition::TYPE_STRING) {
                        $type = "character varying($length)";
                    }

                    $queries[] = sprintf(self::ADD_COLUMN_STRING, $block->getTable(), $columnDefinition->name, $type);
                }
            }

            if ($this->truncateTables) {
                $queries[] = sprintf(self::TRUNCATE_TABLE_STRIING, $block->getTable());
            }
        }

        return $queries;
    }

    /**
     * Create queries for data
     * @param Vfk $vfk
     * @return string[]
     */
    private function createDataQueries(Vfk $vfk): array
    {
        $queries = [];
        $blockColumnTypeIndex = [];

        foreach ($vfk->blockRows as $block) {
            $blockColumnTypeIndex[$block->getTable()] = array_map(function (ColumnDefinition $columnDefinition) {
                return $columnDefinition->type;
            }, $block->getContent());
        }

        foreach ($vfk->dataRows as $data) {
            $table = $data->getTable();

            $typedValues = implode(", ", array_map(function ($value, $index) use ($table, $blockColumnTypeIndex) {
                if (empty(trim($value))) {
                    return "null";
                }
                return $blockColumnTypeIndex[$table][$index] === ColumnDefinition::TYPE_NUMBER ? $value : "'$value'";
            }, $data->getContent(), array_keys($data->getContent())));

            $queries[] = sprintf(self::INSERT_DATA_STRING, $table, $typedValues);
        }

        return $queries;
    }

    /**
     * @param string $schema
     */
    public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * @param bool $createTables
     */
    public function setCreateTables(bool $createTables): void
    {
        $this->createTables = $createTables;
    }

    /**
     * @param bool $truncateTables
     */
    public function setTruncateTables(bool $truncateTables): void
    {
        $this->truncateTables = $truncateTables;
    }
}
