<?php

namespace VfkImporter;

/**
 * Interface IExecutor
 *
 * @author Jiri Kyncl
 */
interface IExecutor
{
    /**
     * Execute VFK
     * @param Vfk $vfk
     */
    public function execute(Vfk $vfk): void;
}
