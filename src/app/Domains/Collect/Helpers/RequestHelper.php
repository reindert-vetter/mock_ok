<?php
declare(strict_types=1);


namespace App\Domains\Collect\Helpers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderBag;

class RequestHelper
{
    public static function normalizeRequest(Request $request): Request
    {
        // Remove twins in host header
        $host = RequestHelper::removeTwinsHost($request->headers->get('host'));
        if ($host == '' || $host == 'localhost') {
            $url  = RequestHelper::removeTwinsHost($request->url());
            $url = str_replace_first('https://', '', $url);
            $url = str_replace_first('http://', '', $url);
            $host = strtok($url, '/');
        }
        $request->headers->set('host', $host);

        // Set base url
        $request->server->set('HTTP_HOST', RequestHelper::removeTwinsHost($request->url()));

        return $request;
    }
    /**
     * @param Request $request
     * @return array
     */
    public static function normalizeHeaders(Request $request): array
    {
        $headers = collect($request->headers);

        // Normalize multidimensional array
        $headers->transform(function ($item) {
            return $item[0];
        });

        if ("" === $headers['content-length']) {
            unset($headers['content-length']);
        }

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
        $subject = str_replace_first('.twins.dev.myparcel.nl', '', $subject);
        return str_replace_first('.localhost', '', $subject);
    }
}
