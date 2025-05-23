<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitae8f52ba12e5694de9ab1d99be95fb91
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'SecurionPay\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'SecurionPay\\' => 
        array (
            0 => __DIR__ . '/..' . '/securionpay/securionpay-php/lib/SecurionPay',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitae8f52ba12e5694de9ab1d99be95fb91::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitae8f52ba12e5694de9ab1d99be95fb91::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitae8f52ba12e5694de9ab1d99be95fb91::$classMap;

        }, null, ClassLoader::class);
    }
}
