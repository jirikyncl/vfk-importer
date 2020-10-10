<?php

namespace VfkImporter;

/**
 * Class DataRow
 *
 * @author Jiri Kyncl
 */
class DataRow implements IRow
{
    /** @var string */
    private $table;

    /** @var string[] */
    private $data;

    /**
     * DataRow constructor.
     * @param string $table
     * @param string[] $data
     */
    public function __construct(string $table, array $data)
    {
        $this->table = $table;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string[]
     */
    public function getContent(): array
    {
        return $this->data;
    }
}
