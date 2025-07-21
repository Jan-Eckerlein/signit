<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class TestWatch extends Command
{
    protected $signature = 'test:watch';
    protected $description = 'Run phpunit-watcher with deprecation warnings suppressed';

    public function handle(): void
    {
        $process = Process::fromShellCommandline(
            'php -d error_reporting=E_ALL^E_DEPRECATED vendor/bin/phpunit-watcher watch'
        );
        $process->setTty(true); // Allows interactive output
        $process->run();
    }
}