<?php
declare(strict_types=1);

namespace App\Domains\Collect\Service;

use Illuminate\Http\Request;

/**
 * @author Reindert Vetter <reindert@myparcel.nl>
 */
class UrlConverter
{
    public function handle(Request $request): Request
    {
        $newRequest = $request->duplicate();
        $newRequest->headers->set('host', 'myparcel.nl');

        return $newRequest;
    }
}