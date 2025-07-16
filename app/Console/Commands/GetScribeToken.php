<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetScribeToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-scribe-token {--raw}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (!file_exists(storage_path('app/private/scribe-token.txt'))) {
            echo "Scribe token not found, Run the following command to generate a new one:\n";
            echo "php artisan db:seed --class=ScribeUserSeeder\n";
            return;
        }
    
        $token = trim(file_get_contents(storage_path('app/private/scribe-token.txt')));
        $this->info($this->option('raw') ? $token : "Scribe token: $token");
    }
}

