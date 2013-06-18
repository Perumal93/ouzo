<?php
namespace Thulium\Utilities;

class Objects
{
    public static function toString($var)
    {
        switch (gettype($var)) {
            case 'boolean':
                return self::booleanToString($var);
            case 'NULL':
                return "null";
            case 'string':
                return "\"$var\"";
            case 'object':
                return self::objectToString($var);
            case 'array':
                return self::arrayToString($var);
        }
        return "$var";
    }

    private static function objectToString($object)
    {
        $array = get_object_vars($object);
        $elements = self::stringifyArrayElements($array);
        return '{' . implode(', ', $elements) . '}';
    }

    private static function stringifyArrayElements($array)
    {
        $elements = array();
        $isAssociative = array_keys($array) !== range(0, sizeof($array) - 1);
        array_walk($array, function ($element, $key) use (&$elements, $isAssociative) {
            if ($isAssociative)
                $elements[] = "<$key> => " . Objects::toString($element);
            else
                $elements[] = Objects::toString($element);
        });
        return $elements;
    }

    private static function arrayToString(array $array)
    {
        $elements = self::stringifyArrayElements($array);
        return '[' . implode(', ', $elements) . ']';
    }

    public static function booleanToString($var)
    {
        return $var ? 'true' : 'false';
    }

    public static function getFieldRecursively($object, $names)
    {
        $fields = explode('->', $names);
        foreach ($fields as $field) {
            if (!$object->$field) {
                return null;
            }
            $object = $object->$field;
        }
        return $object;
    }
}