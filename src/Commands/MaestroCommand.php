<?php

namespace Flowcoders\Maestro\Commands;

use Illuminate\Console\Command;

class MaestroCommand extends Command
{
    public $signature = 'maestro';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
