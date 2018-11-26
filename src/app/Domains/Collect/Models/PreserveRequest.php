<?php
declare(strict_types=1);


namespace App\Domains\Collect\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property string         method
 * @property string         uri
 * @property array          query
 * @property string         body
 * @property array          headers
 * @property string         hash
 * @property PreserveResult result
 */
class PreserveRequest extends Model
{
    protected $fillable = [
        'method',
        'uri',
        'query',
        'body',
        'headers',
        'hash',
    ];

    protected $casts = [
        'headers' => 'array',
        'query'   => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        $this->headers = $this->normalizeHeaders($attributes['headers']);
        $this->uri     = $this->removeTwinsHost($attributes['uri']);
        unset($attributes['headers'], $attributes['uri']);

        parent::__construct($attributes);
    }

    public function result()
    {
        return $this->hasOne(PreserveResult::class);
    }

    /**
     * @param $subject
     * @return string
     */
    private function removeTwinsHost($subject): string
    {
        return str_replace_first('.localhost/dev', '', $subject);
    }

    /**
     * @param Collection $headers
     * @return array
     */
    private function normalizeHeaders(Collection $headers): array
    {
        // Normalize multidimensional array
        $headers->transform(function ($item) {
            return $item[0];
        });

        // Remove twins in host header
        $headers->put('host', $this->removeTwinsHost($headers->get('host')));

        return $headers->toArray();
    }
}