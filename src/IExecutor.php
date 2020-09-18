<?php


namespace VfkImporter;


interface IExecutor
{
    public function execute(array $blockRows, array $dataRows): void;
}