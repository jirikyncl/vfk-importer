<?php


namespace VfkImporter;


class BlockRow implements IRow
{
    const TYPE = 'B';

    private $table;

    private $columnDefinitions;

    public function __construct(string $table, array $columnDefinitions)
    {
        $this->table = $table;
        $this->columnDefinitions = array_map(function (string $colDef) {
            return ColumnDefinition::fromString($colDef);
        }, $columnDefinitions);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getContent(): array
    {
        return $this->columnDefinitions;
    }
}
