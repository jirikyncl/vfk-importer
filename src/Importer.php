<?php

namespace VfkImporter;

/**
 * Class Importer
 *
 * @author Jiri Kyncl
 */
class Importer
{
    /** @var IExecutor */
    private $executor;

    /**
     * Importer constructor.
     * @param IExecutor $executor
     */
    public function __construct(IExecutor $executor)
    {
        $this->executor = $executor;
    }

    /**
     * Run import
     * @param string $filePath
     */
    public function run(string $filePath): void
    {
        $parser = new Parser(file($filePath));
        $vfk = new Vfk();
        $vfk->blockRows = $parser->getParsedRows(BlockRow::class);
        $vfk->dataRows = $dataRows = $parser->getParsedRows(DataRow::class);
        $this->executor->execute($vfk);
    }
}
