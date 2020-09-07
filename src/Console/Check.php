<?php

namespace ProtoneMedia\ApiHealth\Console;

use Illuminate\Console\Command;
use ProtoneMedia\ApiHealth\Checkers\Executor;

class Check extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-health:check {checkerClass}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the given checker';

    /**
     * Displays the result of the given checker.
     *
     * @return null
     */
    public function handle()
    {
        $executor = Executor::make(
            $checkerClass = $this->argument('checkerClass')
        );

        $this->info('Running checker: ' . $checkerClass);

        if ($executor->passes()) {
            $this->info('Passes!');
        } else {
            $this->error('Fails! ' . $executor->getException()->getMessage());
        }
    }
}
