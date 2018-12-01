<?php
declare(strict_types=1);


namespace App\Domains\Collect\Helpers;

use Illuminate\Support\Collection;

class RequestHelper
{

    /**
     * @param Collection $headers
     * @return array
     */
    public static function normalizeHeaders(Collection $headers): array
    {
        // Normalize multidimensional array
        $headers->transform(function ($item) {
            return $item[0];
        });

        if ("" === $headers['content-length']) {
            unset($headers['content-length']);
        }

        // Remove twins in host header
        $headers->put('host', RequestHelper::removeTwinsHost($headers->get('host')));

        return $headers->toArray();
    }

    /**
     * @param $subject
     * @return string
     */
    public static function removeTwinsHost($subject): string
    {
        return str_replace_first('.localhost', '', $subject);
    }
}