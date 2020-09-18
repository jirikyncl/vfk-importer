<?php


namespace VfkImporter;


class Parser
{
    const FORBIDDEN_HEADER_DATA_ROWS = [
        "katuze"
    ];

    private $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function getRows(string $type)
    {
        $rows = [];
        foreach ($this->rows as $row) {
            $row = utf8_encode($row);
            if (strpos($row, "&$type") === 0) {
                $rowArray = explode(";", str_replace('"', "", $row));
                $table = strtolower(str_replace("&" . $type, "", $rowArray[0]));

                if (in_array($table, self::FORBIDDEN_HEADER_DATA_ROWS)) {
                    continue;
                }

                $data = array_slice($rowArray, 1);
                $class = (BlockRow::TYPE === $type ? BlockRow::class : DataRow::class);
                $rows[] = new $class($table, $data);
            }
        }
        return $rows;
    }
}
