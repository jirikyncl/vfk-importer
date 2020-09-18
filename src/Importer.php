<?php


namespace VfkImporter;


class Importer
{
    private $executor;

    public function __construct(IExecutor $executor)
    {
        $this->executor = $executor;
    }

    public function run(string $filePath, string $schema = '', bool $createTables = false): void
    {
        $parser = new Parser(file($filePath));

        $this->executor->execute(
            $parser->getRows(BlockRow::TYPE),
            $dataRows = $parser->getRows(DataRow::TYPE)
        );
    }
}
