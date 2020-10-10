<?php

namespace VfkImporter;

/**
 * Interface IRow
 *
 * @author Jiri Kyncl
 */
interface IRow
{
    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * @return array
     */
    public function getContent(): array;
}
