<?php

namespace Vormkracht10\LaravelGpt\Commands;

use Illuminate\Console\Command;

class LaravelGptCommand extends Command
{
    public $signature = 'laravel-gpt';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
