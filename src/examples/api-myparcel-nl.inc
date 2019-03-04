<?php

use Illuminate\Http\Request;

return [

    'when' => function (Request $request): bool {
        return
            $request->isMethod('GET') &&
            preg_match('#^https?\://api\.myparcel\.nl$#', $request->fullUrl());
    },

    'response' => [
        'status'  => 200,
        'headers' => [
            'x-real-ip' => '172.22.0.1',
            'x-forwarded-server' => 'bf729ba5c4dd',
            'x-forwarded-proto' => 'http',
            'x-forwarded-port' => '80',
            'x-forwarded-host' => 'api.myparcel.nl.twins.dev.myparcel.nl',
            'x-forwarded-for' => '172.22.0.1',
            'upgrade-insecure-requests' => '1',
            'cookie' => '_ga=GA1.2.155816483.1546509343; wham_lastchristmas=1; proxy_myparcel_backoffice=1; _gcl_au=1.1.1898694060.1551170734',
            'cache-control' => 'max-age=0',
            'accept-language' => 'nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7,sw;q=0.6',
            'accept-encoding' => 'gzip, deflate',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.119 Safari/537.36',
            'host' => 'api.myparcel.nl',
            'content-type' => '',
        ],
        'body'    =>  /** @lang JSON */'
{
  "title": "MyParcel API",
  "status": "OK"
}
',
    ],

];
