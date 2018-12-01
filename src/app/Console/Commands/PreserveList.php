<?php

namespace App\Console\Commands;

use App\Domains\Collect\Models\PreserveRequest;
use Illuminate\Console\Command;

class PreserveList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'preserve:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows all saved requests and responses';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $requests = PreserveRequest::with('preserveResponse')->get();

        $rows = $requests->map(function (PreserveRequest $item) {
            return [
                'id'     => $item->id,
                'method' => $item->method,
                'uri' => $item->uri,
                'status' => $item->preserveResponse->status,
            ];
        });

        $this->table(
            ['id', 'method', 'url', 'status'],
            $rows
        );
    }
}
