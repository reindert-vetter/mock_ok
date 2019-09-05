<?php declare(strict_types=1);

namespace App\Domains\Collect\Service;

use App\Console\Helpers\Json;
use App\Domains\Collect\Helpers\ResponseHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class MockService
{
    const REQUEST_MOCKED_PATH     = 'examples/response/';
    const REQUEST_MOCKED_PATH_APP = 'app/' . self::REQUEST_MOCKED_PATH;

    /**
     * @param \Illuminate\Http\Request            $consumerRequest
     * @param \Psr\Http\Message\ResponseInterface $clientResponse
     * @throws \Throwable
     */
    public function saveMock(Request $consumerRequest, ResponseInterface $clientResponse): void
    {
        $responseBody = (string) $clientResponse->getBody();
        $langIde      = Json::isJson($responseBody) ? 'JSON' : 'XML';

        $url = $this->getRegexUrl($consumerRequest);
        $requestBody = $this->getRegexBody($consumerRequest);

        $with = [
            "method"       => $consumerRequest->getMethod(),
            "url"          => $url,
            "status"       => $clientResponse->getStatusCode(),
            "requestBody"  => $requestBody,
            "responseBody" => str_replace("'", "\\'", $responseBody),
            "headers"      => ResponseHelper::normalizeHeaders($clientResponse->getHeaders(), strlen($responseBody)),
        ];

        $content = "<?php\n\n" . view('body-template')
                ->with($with)
                ->render();
        $content = str_replace('LANG_IDE', "/** @lang $langIde */", $content);

        $path = $this->getFilePath($consumerRequest);
        if (Storage::exists($path)) {
            $path = $this->getFilePath($consumerRequest, '.double_' . now());
        }

        Storage::put($path, $content);
    }

    /**
     * @param \Illuminate\Http\Request $consumerRequest
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function tryMock(Request $consumerRequest): ?Response
    {
        $mocks = $this->getMocks();

        $matchedMocks = $mocks->filter(
            function ($path) use ($consumerRequest) {
                if (false === strpos($path, '.inc')) {
                    return false;
                }

                $mockPath = storage_path($path);
                $mock     = require($mockPath);

                if (! is_array($mock) || empty($mock['when'])) {
                    return false;
                }

                return call_user_func($mock['when'], $consumerRequest);
            }
        );

        if ($matchedMocks->isEmpty()) {
            return null;
        }

        if ($matchedMocks->count() > 1) {
            $pathMocks = str_replace(base_path() . self::REQUEST_MOCKED_PATH_APP, '', $matchedMocks->pluck('path')->implode(", \n"));
            throw new Exception("Multiple mocks have a match: \n" . $pathMocks);
        }

        $path = $matchedMocks->first();

        Log::debug("Mock found: $path");

        /** @noinspection PhpUnusedLocalVariableInspection */
        $transport = $this->getTransport();
        $mock      = require(storage_path($path));

        $mockedResponse = call_user_func($mock['response'], $transport);
        $response  = new Response(
            $mockedResponse['body'],
            $mockedResponse['status'],
            ResponseHelper::normalizeHeaders($mockedResponse['headers'], strlen($mockedResponse['body']))
        );

        return $response->setContent($mockedResponse['body']);
    }

    /**
     * @return Collection
     */
    private function getMocks(): Collection
    {
        $dir = self::REQUEST_MOCKED_PATH;

        $files = collect(Storage::allFiles($dir));

        $files->transform(function ($path) {
            return 'app/' . $path;
        });

        return $files;
    }

    /**
     * @param Request $consumerRequest
     * @return string
     */
    private function getRegexUrl(Request $consumerRequest): string
    {
        $url = $consumerRequest->fullUrl();

        $regexUrl = preg_quote(html_entity_decode($url), '#');
        return str_replace(['https\:', 'http\:'], 'https?\:', $regexUrl);
    }

    /**
     * @param Request $consumerRequest
     * @return string
     */
    private function getRegexBody(Request $consumerRequest): string
    {
        $content = (string) $consumerRequest->getContent();

        return preg_quote(html_entity_decode($content), '#');
    }

    /**
     * @param string $value
     * @return string
     */
    private function getSlug(string $value): string
    {
        $value = Str::snake($value);
        return Str::slug(
            trim(str_replace(['.', '/', '?', '=', '&', 'https', 'http', 'www', 'api.', '/api'], '_', $value), '_')
        );
    }

    /**
     * @param Request $consumerRequest
     * @param string  $suffix
     * @return string
     */
    private function getFilePath(Request $consumerRequest, string $suffix = ''): string
    {
        preg_match('/(?<service>[\w-]+).\w{2,10}$/', $consumerRequest->getHost(), $match);
        $service  = Str::kebab($match['service']);
        $fileName = $consumerRequest->method() . '_' . $this->getSlug(pathinfo($consumerRequest->getUri())['basename']);
        $fileName = substr($fileName, 0, 100);

        $path     = self::REQUEST_MOCKED_PATH . "$service/$fileName$suffix.inc";

        return $path;
    }

    /**
     * @return Collection
     */
    protected function getTransport(): Collection
    {
        $data = collect();
        $path = storage_path(self::REQUEST_MOCKED_PATH_APP . '.twins/transport.json');

        if (file_exists($path)) {
            $contents = file_get_contents($path);
            $data = collect(\GuzzleHttp\json_decode($contents));
        }

        return $data;
    }
}
