<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4305c280663e909e5e122aadc9a169d7
{
    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'Moneris' => 
            array (
                0 => __DIR__ . '/../..' . '/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit4305c280663e909e5e122aadc9a169d7::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
