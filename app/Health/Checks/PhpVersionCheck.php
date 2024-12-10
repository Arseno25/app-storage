<?php

namespace App\Health\Checks;

use App\Health\Checks\DependenciesCheck\Dependencies\PhpVersion;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class PhpVersionCheck extends Check
{
    protected ?string $name = 'PHP Version Check';

    public function run(): Result
    {
        $phpVersionName = PhpVersion::getName();

        $result = Result::make()
            ->meta(['php_version' => PHP_VERSION])
            ->shortSummary($phpVersionName);

        if (PhpVersion::isSupported()) {
            return $result->ok();
        }

        return $result->failed($phpVersionName);
    }
}
