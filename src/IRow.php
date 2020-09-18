<?php


namespace VfkImporter;


interface IRow
{
    public function getTable(): string;

    public function getContent(): array;
}