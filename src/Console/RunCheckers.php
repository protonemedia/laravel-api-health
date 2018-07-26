<?php

namespace Pbmedia\ApiHealth\Console;

use Illuminate\Console\Command;
use Pbmedia\ApiHealth\Runner;

class RunCheckers extends Command
{
    protected $signature = 'api-health:run-checkers';

    protected $description = 'Run the checkers';

    protected $runner;

    public function __construct(Runner $runner)
    {
        parent::__construct();

        $this->runner = $runner;
    }

    public function handle()
    {
        $failed = $this->runner->failed();

        $passes = $this->runner->passes();

        $this->info('Total checkers run: ' . ($failed->count() + $passes->count()));

        if ($passes->isNotEmpty()) {
            $this->info('Passing checkers:');
            $this->table(['Checker'], $passes->map(function ($checker) {
                return [get_class($checker)];
            }));
        }

        if ($failed->isNotEmpty()) {
            $this->info('Failed checkers:');
            $this->table(['Checker', 'Exception'], $failed->map(function ($checkerAndException) {
                return [get_class($checkerAndException[0]), $checkerAndException[1]->getMessage()];
            }));
        }
    }
}
