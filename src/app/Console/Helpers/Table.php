<?php
declare(strict_types=1);


namespace App\Console\Helpers;

use App\Domains\Collect\Models\PreserveRequest;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class Table
{
    /**
     * @param Collection $requests
     * @param Command    $terminal
     */
    public static function show(Collection $requests, Command $terminal): void
    {
        $rows = $requests->map(function (PreserveRequest $item) {
            return [
                'id'     => $item->id,
                'method' => $item->method,
                'uri'    => $item->uri,
                'status' => $item->preserveResponse->status,
            ];
        });

        $terminal->table(
            ['id', 'method', 'url', 'status'],
            $rows
        );
    }
}