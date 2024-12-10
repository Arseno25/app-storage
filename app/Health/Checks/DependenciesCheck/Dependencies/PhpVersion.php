<?php

namespace App\Health\Checks\DependenciesCheck\Dependencies;

use App\Health\Checks\DependenciesCheck\Contracts\PhpVersionCheck;

class PhpVersion implements PhpVersionCheck
{
    private const MIN_VERSION = '8.2.0';

    public static function isSupported(): bool
    {
        return version_compare(PHP_VERSION, self::MIN_VERSION, '>=');
    }

    public static function getName(): string
    {
        if (self::isSupported()) {
            return 'PHP version '.PHP_VERSION.' is supported.';
        }

        return 'PHP version '.PHP_VERSION.' is not supported. Required: '.self::MIN_VERSION.' or higher.';
    }
}
