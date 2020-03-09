<?php

namespace abp\component;

/**
 * Class Resource
 * @package app\component
 */
class Resource
{
    const RESOURCE_FOLDER = 'resourse';

    const DEFAULT_HASH = '3245e2b9233a48c1e5274a8eb5d6863f';

    const EXTENSION_CSS = 'css';
    const EXTENSION_JS = 'js';

    const TYPE_CSS = 'text/css';
    const TYPE_JS = 'text/javascript';

    const DEPENDS_BOOTSTRAP = 'bootstrap';
    const DEPENDS_JQUERY = 'jquery';

    public static function registerFile($file, $type = null, $return = false)
    {
        $resource = '';

        if ($type == null) {
            $extension = explode('.', $file);
            $extension = $extension[count($extension) - 1];
            $type = self::convertExtensionInType($extension);
        } else {
            $type = self::convertExtensionInType($type);
        }

        switch ($type) {
            case self::TYPE_CSS:
                $resource = '<link rel="stylesheet" type="'. $type . '" href="/'. self::RESOURCE_FOLDER . '/' . $file . '?hash=' . self::getTimeFileChange(self::RESOURCE_FOLDER . '/' . $file) . '">';
                switch ($file) {
                    case self::DEPENDS_BOOTSTRAP:
                        $resource = '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">';
                        break;
                }
                    break;
            case self::TYPE_JS:
                $resource = '<script type="'. $type . '" src="/'. self::RESOURCE_FOLDER . '/' . $file . '?hash=' . self::getTimeFileChange(self::RESOURCE_FOLDER . '/' . $file) . '"></script>';
                switch ($file) {
                    case self::DEPENDS_BOOTSTRAP:
                        $resource = '<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>';
                        $resource .= '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>';
                        break;
                    case self::DEPENDS_JQUERY:
                        $resource = '<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>';
                        break;
                }
        }

        if ($return) {
            return $resource;
        }

        echo $resource;
    }

    public static function register($files)
    {
        foreach ($files as $file) {
            if (!isset($file['file'])) {
                continue;
            }
            echo self::registerFile($file['file'], $file['type'] ?? null, true);
        }
    }

    private static function convertExtensionInType($extension)
    {
        switch ($extension) {
            case self::EXTENSION_CSS:
                return self::TYPE_CSS;
            case self::EXTENSION_JS:
                return self::TYPE_JS;
        }

        return $extension;
    }

    private static function getTimeFileChange($file)
    {
        if (!file_exists($file)) {
            return self::DEFAULT_HASH;
        }

        return md5(filectime($file));

    }
}