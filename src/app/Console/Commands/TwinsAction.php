<?php

namespace App\Console\Commands;

use App\Console\Helpers\SearchWithAction;
use Illuminate\Console\Command;

class TwinsAction extends Command
{
    const SHOW_DETAILS       = 'Show details';
    const REMOVE             = 'Remove';
    const SHOW_RESPONSE_BODY = 'Show response body';
    const MAX_STRING_LENGTH  = 100;
    const END                = '...';
    const SEARCH_AGAIN       = 'Search again';
    const MENU               = 'menu';
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
     */
    public function handle()
    {
        SearchWithAction::searchWithAction($this);
    }
}
