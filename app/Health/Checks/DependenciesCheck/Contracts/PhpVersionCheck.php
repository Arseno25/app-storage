<?php

namespace App\Health\Checks\DependenciesCheck\Contracts;

interface PhpVersionCheck
{
    public static function isSupported(): bool;

    public static function getName(): string;
}
