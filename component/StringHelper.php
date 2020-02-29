<?php

namespace abp\component;

class StringHelper
{
    const MIN_UPPER_CASE_CODE = 65;
    const MAX_UPPER_CASE_CODE = 90;

    public static function conversionFilename($string)
    {
        $temp = explode('\\', $string);
        $modelName = array_pop($temp);
        $length = strlen($modelName);
        $newString = mb_strtolower($modelName[0]);
        for ($i = 1 ; $i < $length; $i++) {
            $code = ord($modelName[$i]);
            if ($code >= self::MIN_UPPER_CASE_CODE && $code <= self::MAX_UPPER_CASE_CODE) {
                $newString = $newString . '_';
                $modelName[$i] = mb_strtolower($modelName[$i]);
            }
            $newString .= $modelName[$i];
        }
        return $newString;
    }

    public static function revertConversionFilename($string)
    {
        $tableName = $string;
        $length = strlen($tableName);
        $newString = ucfirst($tableName[0]);
        for ($i = 1 ; $i < $length; $i++) {
            if ($tableName[$i] == '_') {
                $newString[$i] = ucfirst($tableName[$i + 1]);
                $i++;
                continue;
            }

            $newString .= $tableName[$i];
        }
        return $newString;
    }

    public static function checkUpperCaseFirstLetter($string)
    {
        $code = ord($string[$i]);
        if ($code >= self::MIN_UPPER_CASE_CODE && $code <= self::MAX_UPPER_CASE_CODE) {
            return true;
        }
        return false;
    }

    public static function parseRequest($string, $admin, $api)
    {
        $temp = explode('/', $string);
        if ($admin) {
            if ($temp[1] === $admin) {
                if (count($temp) !== 4) {
                    return false;
                }
                $newTemp[0] = $temp[0];
                $newTemp[1] = $temp[2];
                $newTemp[2] = $temp[3];
                $temp = $newTemp;
            }
        }
        if ($api) {
            if ($temp[1] === $api) {
                if (count($temp) !== 4) {
                    return false;
                }
                $newTemp[0] = $temp[0];
                $newTemp[1] = $temp[2];
                $newTemp[2] = $temp[3];
                $temp = $newTemp;
            }
        }
        if (count($temp) < 2 || count($temp) > 4) {
            return false;
        }
        if (count($temp) === 2) {
            if ($temp[1] === '') {
                return [
                    'origin' => [
                        'controller' => 'default',
                        'action' => 'index',
                    ],
                    'parse' => [
                        'controller' => self::conversionController('default'),
                        'action' => self::conversionAction('index'),
                    ],
                ];
            }
            return [
                'origin' => [
                    'controller' => $temp[1],
                    'action' => 'index',
                ],
                'parse' => [
                    'controller' => self::conversionController($temp[1]),
                    'action' => self::conversionAction('index'),
                ],
            ];

        }
        if (count($temp) === 3) {
            if ($temp[2] === '') {
                return [
                    'origin' => [
                        'controller' => $temp[1],
                        'action' => 'index',
                    ],
                    'parse' => [
                        'controller' => self::conversionController($temp[1]),
                        'action' => self::conversionAction('index'),
                    ],
                ];
            }
            return [
                'origin' => [
                    'controller' => $temp[1],
                    'action' => $temp[2],
                ],
                'parse' => [
                    'controller' => self::conversionController($temp[1]),
                    'action' => self::conversionAction($temp[2]),
                ],
            ];

        }
    }

    public static function conversionRouterName($string, $type)
    {
        return $string.ucfirst($type);
    }

    public static function conversionController($string)
    {
        return self::conversionRouterName(ucfirst($string), 'controller');
    }

    public static function conversionAction($string)
    {
        return self::conversionRouterName($string, 'action');
    }
}