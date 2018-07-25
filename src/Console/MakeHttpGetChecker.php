<?php

namespace Pbmedia\ApiHealth\Console;

use Illuminate\Console\GeneratorCommand;

class MakeHttpGetChecker extends GeneratorCommand
{
    protected $name = 'make:http-get-checker';

    protected $description = 'Create a HTTP GET checker';

    protected $type = 'Checker';

    protected function getStub()
    {
        return __DIR__ . '/../../stubs/http-get-checker.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Checkers';
    }
}
