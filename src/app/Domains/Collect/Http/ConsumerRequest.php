<?php
declare(strict_types=1);

namespace App\Domains\Collect\Http;


use GuzzleHttp\Psr7\Request;

class ConsumerRequest extends Request
{
 public function __construct(Request $request)
 {
     var_dump($request->getHeaders());
     exit;;
//     $request->headers =
     parent::__construct($request->duplicate(), $request, $attributes, $cookies, $files, $server, $content);
 }
}
