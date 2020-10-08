<?php

namespace abp\component;

use http\Exception\InvalidArgumentException;

/**
 * Class Resource
 * @package app\component
 */
class Resource
{
    private const RESOURCE_FOLDER = 'resource';
    private const DEFAULT_HASH    = '3245e2b9233a48c1e5274a8eb5d6863f';

    private const EXTENSION_CSS   = 'css';
    private const EXTENSION_JS    = 'js';

    private const TYPE_CSS        = 'text/css';
    private const TYPE_JS         = 'text/javascript';

    private const NPM_ASSET = 'npm-asset';

    private const VENDOR_FOLDER = 'vendor';

    private const DEPENDS = [
        'satisfy' => [
            'paths' => [
                self::NPM_ASSET . '/fontsource-satisfy/index.css',
                ],
        ],
        'font-awesome' => [
            'paths' => [
                self::NPM_ASSET . '/font-awesome/css/all.min.css',
                self::NPM_ASSET . '/font-awesome/js/all.min.js',
            ],
        ],
        'bootstrap' => [
            'paths' => [
                self::NPM_ASSET . '/bootstrap/dist/css/bootstrap.min.css',
                self::NPM_ASSET . '/bootstrap/dist/js/bootstrap.min.js',
            ],
        ],
        'jquery' => [
            'paths' => [
                self::NPM_ASSET . '/jquery/dist/jquery.min.js',
            ],
        ],
    ];

    private static $registredFiles = [
        self::EXTENSION_CSS => '',
        self::EXTENSION_JS => '',
    ];

    public static function register(array $resources): void
    {
        foreach ($resources as $resource) {
            if (!isset($resource['resource'])){
                continue;
            }
            self::registerResorce($resource);
        }
        echo self::$registredFiles[self::EXTENSION_CSS];
    }

    private static function registerResorce(array $resource): void
    {
        $type = $resource['type'] ?? null;
        $extension = $type;
        $name = $resource['resource'];

        if ($type == null) {
            $extensionParts = explode('.', $name);
            $extension = end($extensionParts);
            $type = self::convertExtensionInType($extension);
        } else {
            $type = self::convertExtensionInType($type);
        }
        $resourceDefault = self::DEPENDS[$name] ?? null;
        $resourceFolder = \Abp::$root . self::RESOURCE_FOLDER;

        if ($resourceDefault !== null) {
            $vendorFolder = \Abp::$root . self::VENDOR_FOLDER;
            foreach ($resourceDefault['paths'] as $localPath) {
                $pathToResource = $vendorFolder .  '/' . $localPath;
                $parsePath = explode('/', $localPath);
                $localPath = $parsePath[1];
                $pathToResourceParts = explode('.', $pathToResource);
                $extension = end($pathToResourceParts);
                self::saveHTML(self::convertExtensionInType($extension), self::createResource($pathToResource, $localPath));
            }
        } else {
            $pathToResource = $resourceFolder .  '/' . $extension . '/' . $name;
            self::saveHTML($type, self::createResource($pathToResource, $extension));
        }
    }

    private static function createResource(string $pathToResource, string $localPath): string
    {
        $resourceFolderRuntime = \Abp::$root . self::RESOURCE_FOLDER . '/runtime/';

        if (!file_exists($pathToResource)) {
            throw new \InvalidArgumentException("Invalid path to resource: $pathToResource");
        }
        $runtimeFolderHash = self::getHash($localPath);
        $lastModifyTimeHash = self::getTimeFileChange($pathToResource);
        $partPathToResource = explode('.', $pathToResource);
        $extension = end($partPathToResource);
        $fileWebAccess = "runtime/$runtimeFolderHash/$lastModifyTimeHash.$extension";
        $runtimeFolderPath = $resourceFolderRuntime . $runtimeFolderHash;
        $runtimeFilePath = "$runtimeFolderPath/$lastModifyTimeHash.$extension";
        if (file_exists($runtimeFilePath)) {
            return $fileWebAccess;
        }
        if (!is_dir($runtimeFolderPath)) {
            mkdir($runtimeFolderPath, 0777);
        }
        copy($pathToResource, $runtimeFilePath);
        return $fileWebAccess;
    }

    private static function saveHTML(string $type, string $file): void
    {
        switch ($type) {
            case self::TYPE_CSS:
                self::$registredFiles[self::EXTENSION_CSS] .= '<link rel="stylesheet" type="'. $type . '" href="/'. self::RESOURCE_FOLDER . '/' . $file . '">';
                break;
            case self::TYPE_JS:
                self::$registredFiles[self::EXTENSION_JS] .= '<script type="'. $type . '" src="/'. self::RESOURCE_FOLDER . '/' . $file . '"></script>';
                break;
            default:
                break;
        }
    }

    public static function printRegistredJsFiles(): void
    {
        echo self::$registredFiles[self::EXTENSION_JS];
    }

    private static function convertExtensionInType($extension): string
    {
        switch ($extension) {
            case self::EXTENSION_CSS:
                return self::TYPE_CSS;
            case self::EXTENSION_JS:
                return self::TYPE_JS;
        }

        return $extension;
    }

    private static function getTimeFileChange(string $path): string
    {
        return self::getHash((string) filectime($path));
    }

    private static function getHash(string $string): string
    {
        return substr(md5($string), 0 , 10);
    }
}