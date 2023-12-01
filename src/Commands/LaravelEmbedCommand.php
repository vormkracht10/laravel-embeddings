<?php

namespace Vormkracht10\Embedding\Commands;

use Illuminate\Console\Command;

class LaravelEmbedCommand extends Command
{
    public $signature = 'laravel-embed';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
