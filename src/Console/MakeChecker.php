<?php

namespace Pbmedia\ApiHealth\Console;

use Illuminate\Console\GeneratorCommand;

class MakeChecker extends GeneratorCommand
{
    protected $name = 'make:checker';

    protected $description = 'Create a checker';

    protected $type = 'Checker';

    protected function getStub()
    {
        return __DIR__ . '/../../stubs/checker.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Checkers';
    }
}
