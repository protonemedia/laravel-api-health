<?php

namespace Pbmedia\ApiHealth\Console;

use Illuminate\Console\GeneratorCommand;

class MakeHttpGetChecker extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:http-get-checker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a HTTP GET checker';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Checker';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../stubs/http-get-checker.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Checkers';
    }
}
