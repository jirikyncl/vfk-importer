<?php

namespace VfkImporter;

/**
 * Class ColumnDefinition
 *
 * @author Jiri Kyncl
 */
class ColumnDefinition
{
    const TYPE_NUMBER = 'N';
    const TYPE_STRING = 'T';
    const TYPE_DATE = 'D';

    /** @var string */
    public $type;

    /** @var int */
    public $length;

    /** @var string */
    public $name;

    /**
     * Factory method
     * @param string $colDefString
     * @return ColumnDefinition
     */
    public static function createFromString(string $colDefString)
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
