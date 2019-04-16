<?php
declare(strict_types=1);

namespace App\Domains\Collect\Helpers;

class ResponseHelper
{
    /**
     * @param array $headers
     * @param int   $contentLength
     * @return array
     */
    public static function normalizeHeaders(array $headers, int $contentLength): array
    {
        $headers = collect($headers);

        if (is_array($headers->first())) {
            // Normalize multidimensional array
            $headers->transform(function ($item) {
                return $item[0];
            });
        }

        $headers['content-length'] = $contentLength;

        return $headers->toArray();
    }
}