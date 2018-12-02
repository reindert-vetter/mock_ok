<?php
declare(strict_types=1);


namespace App\Console\Helpers;

use App\Console\Commands\TwinsAction;

class SearchWithAction
{

    /**
     * @param TwinsAction $terminal
     * @throws \Exception
     */
    public static function searchWithAction($terminal): void
    {
        $chosenItem = Search::search($terminal);

        $terminal->table([], [
            ['id', $chosenItem->id],
            ['method', str_limit($chosenItem->method, TwinsAction::MAX_STRING_LENGTH, TwinsAction::END)],
            ['uri', str_limit($chosenItem->uri, TwinsAction::MAX_STRING_LENGTH, TwinsAction::END)],
            ['accept', str_limit(data_get($chosenItem, 'headers.accept'), TwinsAction::MAX_STRING_LENGTH, TwinsAction::END)],
            ['status', str_limit($chosenItem->preserveResponse->status, TwinsAction::MAX_STRING_LENGTH, TwinsAction::END)],
            ['response body', str_limit($chosenItem->preserveResponse->body, TwinsAction::MAX_STRING_LENGTH, TwinsAction::END)],
        ]);

        $actionType = $terminal->choice(
            'What action you want to perform?',
            [
                TwinsAction::REMOVE,
                TwinsAction::SHOW_RESPONSE_BODY,
                TwinsAction::SHOW_DETAILS,
                TwinsAction::MENU,
                TwinsAction::SEARCH_AGAIN,
            ],
            0
        );

        switch ($actionType) {
            case TwinsAction::REMOVE:
                $chosenItem->delete();
                $terminal->info('Deleted ' . $chosenItem->id);
                self::searchWithAction($terminal);
                break;
            case TwinsAction::MENU:
                $terminal->error('todo');
                break;
            case TwinsAction::SEARCH_AGAIN:
                self::searchWithAction($terminal);
                break;
            case TwinsAction::SHOW_DETAILS:
                dump(array_dot($chosenItem->toArray()));
                break;
            case TwinsAction::SHOW_RESPONSE_BODY:
                $terminal->line($chosenItem->preserveResponse->body);
                break;
        }
    }
}