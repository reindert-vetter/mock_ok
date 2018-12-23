<?php

namespace App\Console\Commands;

use App\Console\Helpers\SearchWithAction;
use Illuminate\Console\Command;

class TwinsAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find a saved requests and responses';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        SearchWithAction::searchWithAction($this);
    }
}
