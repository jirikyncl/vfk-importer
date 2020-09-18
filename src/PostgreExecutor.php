<?php


namespace VfkImporter;


class PostgreExecutor implements IExecutor
{
    const CONNECTION_STRING = "host=%s port=%s dbname=%s user=%s password=%s";
    const CREATE_TABLE_STRING = "CREATE TABLE IF NOT EXISTS %s ();";
    const ADD_COLUMN_STRING = "ALTER TABLE IF EXISTS %s ADD COLUMN IF NOT EXISTS %s %s;";
    const INSERT_DATA_STRING = "INSERT INTO %s VALUES(%s);";

    private $connection;
    private $schema = 'public';

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

    public function __destruct()
    {
        pg_close($this->connection);
    }

    public function execute(array $blockRows, array $dataRows): void
    {
        $beginQueries = ["SET LOCAL search_path TO $this->schema;"];
        $blockQueries = $this->createBlockQueries($blockRows);
        $dataQueries = $this->createDataQueries($blockRows, $dataRows);
        $initQueries = array_merge($beginQueries, $blockQueries);
        $initQueryString = implode(PHP_EOL, $initQueries);

        pg_query($this->connection, "BEGIN");
        $success = @pg_query($this->connection, $initQueryString);

        // Paging over data inserts
        $chunkedDataQueries = array_chunk($dataQueries, 100);
        foreach ($chunkedDataQueries as $chunk) {
            if (!$success) {
                break;
            }
            $dataQueryString = implode(PHP_EOL, $chunk);
            $success = @pg_query($this->connection, $dataQueryString);
        }

        if ($success) {
            pg_query($this->connection, "COMMIT");
        } else {
            var_dump(pg_last_error($this->connection));
            pg_query($this->connection, "ROLLBACK");
        }
    }

    public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }

    private function createBlockQueries(array $blockRows): array
    {
        $queries = [];

        foreach ($blockRows as $block) {
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

        return $queries;
    }

    private function createDataQueries(array $blockRows, array $dataQueries): array
    {
        $queries = [];
        $blockColumnTypeIndex = [];

        foreach ($blockRows as $block) {
            $blockColumnTypeIndex[$block->getTable()] = array_map(function (ColumnDefinition $columnDefinition) {
                return $columnDefinition->type;
            }, $block->getContent());
        }

        foreach ($dataQueries as $data) {
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
}
