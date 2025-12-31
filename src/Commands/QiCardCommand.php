<?php

namespace Ht3aa\QiCard\Commands;

use Illuminate\Console\Command;

class QiCardCommand extends Command
{
    public $signature = 'qi-card';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
