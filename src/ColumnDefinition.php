<?php


namespace VfkImporter;


class ColumnDefinition
{
    const TYPE_NUMBER = 'N';
    const TYPE_STRING = 'T';
    const TYPE_DATE = 'D';

    public $type;
    public $length;
    public $name;

    public static function fromString(string $colDefString)
    {
        $parts = explode(" ", $colDefString);
        $obj = new ColumnDefinition();
        $obj->name = strtolower($parts[0]);

        if ($parts[1] === self::TYPE_DATE) {
            $obj->type = self::TYPE_DATE;
        } else {
            $obj->type = substr($parts[1], 0, 1);
            $obj->length = substr($parts[1], 1);
        }

        return $obj;
    }
}