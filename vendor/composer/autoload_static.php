<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite6db1d4437f406ff78d1269fa988877b
{
    public static $files = array (
        '9b38cf48e83f5d8f60375221cd213eee' => __DIR__ . '/..' . '/phpstan/phpstan/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Spaze\\PHPStan\\Rules\\Disallowed\\' => 31,
        ),
        'I' => 
        array (
            'IPP\\Student\\' => 12,
            'IPP\\Core\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Spaze\\PHPStan\\Rules\\Disallowed\\' => 
        array (
            0 => __DIR__ . '/..' . '/spaze/phpstan-disallowed-calls/src',
        ),
        'IPP\\Student\\' => 
        array (
            0 => __DIR__ . '/../..' . '/student',
        ),
        'IPP\\Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite6db1d4437f406ff78d1269fa988877b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite6db1d4437f406ff78d1269fa988877b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite6db1d4437f406ff78d1269fa988877b::$classMap;

        }, null, ClassLoader::class);
    }
}
