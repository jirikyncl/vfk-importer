<?php

namespace VfkImporter;

/**
 * Class Parser
 *
 * @author Jiri Kyncl
 */
class Parser
{
    const FORBIDDEN_HEADER_DATA_ROWS = [
        "katuze"
    ];

    private $rows;

    /**
     * Parser constructor.
     * @param string[] $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * Parse VFK rows by type
     * @param string $type
     * @return IRow[]
     */
    public function getParsedRows(string $type)
    {
        $prefix = (BlockRow::class === $type ? "B" : "D");
        $rows = [];
        foreach ($this->rows as $row) {
            $row = utf8_encode(trim($row));
            if (strpos($row, "&$prefix") === 0) {
                $rowArray = explode(";", str_replace('"', "", $row));
                $table = strtolower(str_replace("&" . $prefix, "", $rowArray[0]));

                if (in_array($table, self::FORBIDDEN_HEADER_DATA_ROWS)) {
                    continue;
                }

                $data = array_slice($rowArray, 1);
                $class = (BlockRow::class === $type ? BlockRow::class : DataRow::class);
                $rows[] = new $class($table, $data);
            }
        }
        return $rows;
    }
}
