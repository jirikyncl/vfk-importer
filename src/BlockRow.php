<?php

namespace VfkImporter;

/**
 * Class BlockRow
 *
 * @author Jiri Kyncl
 */
class BlockRow implements IRow
{
    /** @var string */
    private $table;

    /** @var ColumnDefinition[] */
    private $columnDefinitions;

    /**
     * BlockRow constructor.
     * @param string $table
     * @param string[] $columnDefinitions
     */
    public function __construct(string $table, array $columnDefinitions)
    {
        $this->table = $table;
        $this->columnDefinitions = array_map(function (string $colDef) {
            return ColumnDefinition::createFromString($colDef);
        }, $columnDefinitions);
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return ColumnDefinition[]
     */
    public function getContent(): array
    {
        return $this->columnDefinitions;
    }
}
