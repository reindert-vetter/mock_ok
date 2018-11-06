<?php
declare(strict_types=1);

namespace App\Domains\Collect\Service;


use \GuzzleHttp\Client;

/**
 * @author Reindert Vetter <reindert@myparcel.nl>
 */
class Proxy
{
    /**
     * @var UrlConverter
     */
    private $urlConverter;

    public function __construct(UrlConverter $converter)
    {
        $this->urlConverter = $converter;
    }

    public function getReponse(\Illuminate\Http\Request $request)
    {
        $request = $this->urlConverter->handle($request);
        return (new Client())->send($request);
    }
}