<?php
declare(strict_types=1);


namespace App\Console\Helpers;

use App\Console\Commands\TwinsAction;
use App\Domains\Collect\Models\PreserveRequest;

class Search
{

    /**
     * @param TwinsAction $terminal
     * @return PreserveRequest|PreserveRequest[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public static function search($terminal)
    {
        $latestResult = PreserveRequest::with('preserveResponse')->orderBy('created_at')->limit(10)->get();
        Table::show($latestResult, $terminal);

        $search = $terminal->ask("Give an id or search for items (or 'refresh')");

        if ($search === 'refresh') {
            return self::search($terminal);
        }

        if (is_numeric($search)) {
            return PreserveRequest::find($search);
        }

        $searchResult = PreserveRequest::with('preserveResponse')
                                       ->where('uri', 'like', '%' . $search . '%')->get();

        if ($searchResult->count() == 1) {
            return $searchResult->first();
        }

        Table::show($searchResult, $terminal);

        $chosenId = $terminal->ask('Start an action for id:', $searchResult->first()->id);
        return $searchResult->find($chosenId);
    }
}