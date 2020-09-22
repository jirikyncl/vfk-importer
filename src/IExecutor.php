<?php


namespace VfkImporter;


interface IExecutor
{
    public function execute(Vfk $vfk): void;
}