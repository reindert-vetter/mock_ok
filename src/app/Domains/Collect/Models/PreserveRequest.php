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
        if (! empty($attributes)) {
            $this->headers = $this->normalizeHeaders(collect($attributes['headers']));
            $this->uri     = $this->removeTwinsHost($attributes['uri']);
            unset($attributes['headers'], $attributes['uri']);
        }
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
        $subject = str_replace_first('.localhost/dev', '', $subject);
        return str_replace_first('.localhost', '', $subject);
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