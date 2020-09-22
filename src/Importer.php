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

        $vfk = new Vfk();
        $vfk->blockRows = $parser->getRows(BlockRow::TYPE);
        $vfk->dataRows = $dataRows = $parser->getRows(DataRow::TYPE);

        $this->executor->execute($vfk);
    }
}
