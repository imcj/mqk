<?php
namespace MQK;


class CaseConverion
{
    /**
     * Copy from https://stackoverflow.com/questions/2791998/convert-dashes-to-camelcase-in-php
     *
     * @param $string
     * @param bool $capitalizeFirstCharacter
     * @return mixed|string
     */
    public static function snakeToCamel($string, $capitalizeFirstCharacter = true)
    {
        $str = str_replace('_', '', ucwords($string, '_'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }
        return $str;
    }

}