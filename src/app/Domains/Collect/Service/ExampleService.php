<?php declare(strict_types=1);

namespace App\Domains\Collect\Service;

use App\Console\Helpers\Json;
use App\Domains\Collect\Helpers\ResponseHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class ExampleService
{
    const REQUEST_MOCKED_PATH = 'examples/response/';

    /**
     * @param \Illuminate\Http\Request            $consumerRequest
     * @param \Psr\Http\Message\ResponseInterface $clientResponse
     * @throws \Throwable
     */
    public function saveExample(Request $consumerRequest, ResponseInterface $clientResponse): void
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
            "responseBody" => $responseBody,
            "headers"      => ResponseHelper::normalizeHeaders($clientResponse->getHeaders(), strlen($responseBody)),
        ];

        $content = "<?php\n\n" . view('body-template')
                ->with($with)
                ->render();
        $content = str_replace('LANG_IDE', "/** @lang $langIde */", $content);

        $path = $this->getFilePath($consumerRequest);
        if (Storage::exists($path)) {
            throw new Exception("Can't create example $path already exist");
        }

        Storage::put($path, $content);
    }

    /**
     * @param \Illuminate\Http\Request $consumerRequest
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function tryExample(Request $consumerRequest): ?Response
    {
        $examples = $this->getExamples();

        $matchExamples = $examples->filter(
            function ($path) use ($consumerRequest) {
                if (false === strpos($path, '.inc')) {
                    return false;
                }

                $mock = require(base_path('storage/app/' . $path));

                if (! is_array($mock) || empty($mock['when'])) {
                    return false;
                }

                return call_user_func($mock['when'], $consumerRequest);
            }
        );

        if ($matchExamples->isEmpty()) {
            return null;
        }

        if ($matchExamples->count() > 1) {
            $pathExamples = str_replace(base_path() . self::REQUEST_MOCKED_PATH, '', $matchExamples->pluck('path')->implode(", \n"));
            throw new Exception("Multiple examples have a match: \n" . $pathExamples);
        }

        $path = $matchExamples->first();

        $mock     = require(base_path('storage/app/' . $path));
        $response = new Response(
            $mock['response']['body'],
            $mock['response']['status'],
            ResponseHelper::normalizeHeaders($mock['response']['headers'], strlen($mock['response']['body']))
        );

        return $response->setContent($mock['response']['body']);
    }

    /**
     * @return Collection
     */
    private function getExamples(): Collection
    {
        $dir = self::REQUEST_MOCKED_PATH;

        $files = collect(Storage::allFiles($dir));

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
        $value = Str::kebab($value);
        return Str::slug(
            trim(str_replace(['.', '/', '?', '=', '&', 'https', 'http', 'www', 'api.', '/api'], '_', $value), '_')
        );
    }

    /**
     * @param Request $consumerRequest
     * @return string
     */
    private function getFilePath(Request $consumerRequest): string
    {
        preg_match('/(?<service>[\w-]+).\w{2,10}$/', $consumerRequest->getHost(), $match);
        $service  = Str::kebab($match['service']);
        $fileName = $consumerRequest->method() . '_' . $this->getSlug(pathinfo($consumerRequest->getUri())['basename']);
        $path     = self::REQUEST_MOCKED_PATH . "$service/$fileName.inc";

        return $path;
    }
}
