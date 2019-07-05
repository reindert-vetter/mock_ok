<?php
declare(strict_types=1);

namespace App\Domains\Collect\Providers;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Response as IlluminateResponse;
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
        unset($headers['content-length']);
        unset($headers['Content-length']);
        unset($headers['Content-Length']);
        unset($headers['transfer-encoding']);
        $options = [
            'headers' => $headers,
            'query'   => $query,
            'body'    => $body,
            'timeout' => 10,
            'http_errors' => false,
            'verify'      => false, //Self signed certificate used by CheapCargo does not verify
            ['protocols'   => ['http', 'https']],
        ];

        try {
            $result = $client->request($method, $url, $options);
        } catch (Exception $exception) {
            $result = new Response(IlluminateResponse::HTTP_OK, [], $exception->getMessage());
        }

        return $result;
    }
}
