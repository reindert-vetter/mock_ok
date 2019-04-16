<?php
declare(strict_types=1);


namespace App\Domains\Collect\Helpers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderBag;

class RequestHelper
{
    /**
     * @param Request $request
     * @return Request
     */
    public static function normalizeRequest(Request $request): Request
    {
        // Remove twins in host header
        $host = RequestHelper::removeTwinsHost($request->url());
        if ($host == '' || $host == 'localhost') {
            $url  = RequestHelper::removeTwinsHost($request->url());
            $url = str_replace_first('https://', '', $url);
            $url = str_replace_first('http://', '', $url);
            $host = strtok($url, '/');
        }

        $request->headers->set('host', $host);
        $request->headers->set('x-forwarded-host', $host);

        // Set base url
        $request->server->set('HTTP_HOST', RequestHelper::removeTwinsHost($request->url()));
        /** @noinspection PhpUndefinedMethodInspection */
        $request->setCleanPathInfo();

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

    public static function removeHostInUri(string $uri): string
    {
        preg_match('#^\/[^\/]*(.*)#i', $uri, $match);
        return $match[1];
    }

    /**
     * @param $url
     * @return string
     */
    public static function removeTwinsHost($url): string
    {
//        $url = str_replace_first(':81', '', $url);
//        $url = str_replace_first('127.0.0.1', '', $url);
//        $url = str_replace_first('http://', 'https://', $url);
//        $url = str_replace_first('127.0.0.1:80', '', $url);
//        $url = str_replace_first('.twins.dev.myparcel.nl', '', $url);
        $url = str_replace_first('twins/', '', $url);
//        $url = str_replace_first('.localhost', '', $url);
        return parse_url($url, PHP_URL_HOST);
    }
}
