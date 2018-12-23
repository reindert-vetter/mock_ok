<?php
declare(strict_types=1);


namespace App\Domains\Collect\Helpers;

use Illuminate\Support\Collection;

class RequestHelper
{

    /**
     * @param Collection $headers
     * @param string     $url
     * @return array
     */
    public static function normalizeHeaders(Collection $headers, string $url): array
    {
        // Normalize multidimensional array
        $headers->transform(function ($item) {
            return $item[0];
        });

        if ("" === $headers['content-length']) {
            unset($headers['content-length']);
        }

        // Remove twins in host header
        $host = RequestHelper::removeTwinsHost($headers->get('host'));
        if ($host == '' || $host == 'localhost') {
            $url  = RequestHelper::removeTwinsHost($url);
            $url = str_replace_first('https://', '', $url);
            $url = str_replace_first('http://', '', $url);
            $host = strtok($url, '/');
        }
        $headers->put('host', RequestHelper::removeTwinsHost($host));

        return $headers->toArray();
    }

    /**
     * @param $subject
     * @return string
     */
    public static function removeTwinsHost($subject): string
    {
        $subject = str_replace_first(':81', '', $subject);
        $subject = str_replace_first('127.0.0.1', '', $subject);
        $subject = str_replace_first('http://', 'https://', $subject);
        $subject = str_replace_first('127.0.0.1:80', '', $subject);
        $subject = str_replace_first('.localhost', '', $subject);
        return str_replace_first('.localhost', '', $subject);
    }
}