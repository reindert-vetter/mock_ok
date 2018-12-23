<?php
declare(strict_types=1);


namespace App\Console\Helpers;

use App\Console\Commands\TwinsAction;

class SearchWithAction
{
    const SHOW_DETAILS       = 'Show details';
    const REMOVE             = 'Remove';
    const SHOW_RESPONSE_BODY = 'Show response body';
    const MAX_STRING_LENGTH  = 100;
    const MAX_JSON_LENGTH    = 200;
    const MAX_SUFFIX         = '...';
    const SEARCH_AGAIN       = 'Search again';

    /**
     * @param TwinsAction $terminal
     * @throws \Exception
     */
    public static function searchWithAction($terminal): void
    {
        $chosenItem = Search::search($terminal);

        $terminal->table([], [
            ['id', $chosenItem->id],
            ['method', self::limit($chosenItem->method)],
            ['uri', self::limit($chosenItem->uri)],
            ['accept', self::limit(data_get($chosenItem, 'headers.accept'))],
            ['status', self::limit($chosenItem->preserveResponse->status)],
            ['request body', self::limit($chosenItem->body, self::MAX_JSON_LENGTH)],
            ['response body', self::limit(Json::prettyPrint($chosenItem->preserveResponse->body), self::MAX_JSON_LENGTH)],
        ]);

        $actionType = $terminal->choice(
            'What action you want to perform?',
            [
                self::REMOVE,
                self::SHOW_RESPONSE_BODY,
                self::SHOW_DETAILS,
                self::SEARCH_AGAIN,
            ]
        );

        switch ($actionType) {
            case self::REMOVE:
                $chosenItem->delete();
                $terminal->info('Deleted ' . $chosenItem->id);
                self::searchWithAction($terminal);
                break;
            case self::SEARCH_AGAIN:
                self::searchWithAction($terminal);
                break;
            case self::SHOW_DETAILS:
                dump(array_dot($chosenItem->toArray()));
                break;
            case self::SHOW_RESPONSE_BODY:
                $terminal->line($chosenItem->preserveResponse->body);
                break;
        }

        Search::search($terminal);
    }

    private static function limit($value, $limit = self::MAX_SUFFIX)
    {
        return str_limit($value, self::MAX_STRING_LENGTH, $limit);
    }
}