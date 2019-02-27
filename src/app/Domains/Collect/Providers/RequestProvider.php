<?php
declare(strict_types=1);

namespace App\Domains\Collect\Providers;

use App\Domains\Collect\Helpers\RequestHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Reindert Vetter
 */
class RequestProvider
{
    /**
     * @param string $method
     * @param string $url
     * @param array  $query
     * @param string $body
     * @param array  $headers
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function handle(
        string $method,
        string $url,
        array $query,
        string $body,
        array $headers
    ): ResponseInterface {
        $client  = new Client(["http_errors" => false]);
        $options = [
            'headers' => $headers,
            'query'   => $query,
            'body'    => $body,
            'timeout' => 3,
        ];


        $result = $client->request($method, $url, $options);

        return $result;
    }
}
