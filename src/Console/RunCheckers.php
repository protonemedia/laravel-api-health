<?php

namespace Pbmedia\ApiHealth\Console;

use Illuminate\Console\Command;
use Pbmedia\ApiHealth\Checkers\Executor;
use Pbmedia\ApiHealth\Runner;

class RunCheckers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-health:run-checkers {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the configured checkers';

    /**
     * Gets the passing and failing checkers from the Runner
     * and presents them in a table.
     *
     * @param  \Pbmedia\ApiHealth\Runner $runner
     * @return null
     */
    public function handle(Runner $runner)
    {
        if ($this->option('force')) {
            $runner->ignoreScheduling();
        }

        $failed = $runner->failed();

        $passes = $runner->passes();

        $this->info('Total checkers run: ' . ($failed->count() + $passes->count()));

        if ($passes->isNotEmpty()) {
            $this->info('Passing checkers:');
            $this->table(['Checker'], $passes->map(function (Executor $executor) {
                return [get_class($executor->getChecker())];
            }));
        }

        if ($failed->isNotEmpty()) {
            $this->info('Failed checkers:');
            $this->table(['Checker', 'Exception'], $failed->map(function (Executor $executor) {
                return [
                    get_class($executor->getChecker()),
                    $executor->getException()->getMessage(),
                ];
            }));
        }
    }
}
