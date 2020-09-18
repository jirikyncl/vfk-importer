<?php


namespace VfkImporter;


class DataRow implements IRow
{
    const TYPE = 'D';

    private $table;

    private $data;

    public function __construct(string $table, array $data)
    {
        $this->table = $table;
        $this->data = $data;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getContent(): array
    {
        return $this->data;
    }
}
